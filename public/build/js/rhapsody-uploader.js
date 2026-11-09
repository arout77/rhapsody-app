/**
 * Rhapsody Uploader
 *
 * Progressive enhancement for file upload forms. Add `data-rhapsody-upload`
 * (the endpoint URL) to a form that contains a file input and `{{ csrf_field() }}`:
 *
 *   <form data-rhapsody-upload="{{ base_url }}/upload">
 *       {{ csrf_field() }}
 *       <input type="file" name="documents" multiple>
 *       <button type="submit">Upload</button>
 *       <ul data-upload-list></ul>   <!-- optional; created automatically if omitted -->
 *   </form>
 *
 * Each file is sent in its own XMLHttpRequest (XHR exposes upload progress,
 * fetch does not) so every file gets its own progress bar and error message.
 *
 * Styling hooks (no CSS is injected):
 *   li[data-state="waiting|uploading|success|error"]
 *   [data-role="name"], progress, [data-role="status"]
 *
 * Events (bubble from the form):
 *   rhapsody:upload:start     detail: { files }
 *   rhapsody:upload:success   detail: { file, response }
 *   rhapsody:upload:error     detail: { file, message, response }
 *   rhapsody:upload:complete  detail: { results: [{ file, ok, response }] }
 */
(() => {
    'use strict';

    const emit = (form, name, detail) =>
        form.dispatchEvent(new CustomEvent(`rhapsody:upload:${name}`, { bubbles: true, detail }));

    const createItem = (list, fileName) => {
        const item = document.createElement('li');
        item.dataset.state = 'waiting';

        const name = document.createElement('div');
        name.dataset.role = 'name';
        name.textContent = fileName;

        const bar = document.createElement('progress');
        bar.max = 100;
        bar.value = 0;
        bar.setAttribute('aria-label', `Upload progress for ${fileName}`);

        const status = document.createElement('div');
        status.dataset.role = 'status';
        status.textContent = 'Waiting…';

        item.append(name, bar, status);
        list.append(item);

        return {
            progress(percent) {
                item.dataset.state = 'uploading';
                bar.value = percent;
                status.textContent = percent >= 100 ? 'Processing…' : `Uploading… ${Math.round(percent)}%`;
            },
            finish(state, message) {
                item.dataset.state = state;
                if (state === 'success') {
                    bar.value = 100;
                }
                status.textContent = message;
            },
        };
    };

    const errorMessage = (status, body, fileName) => {
        const errors = body?.errors;
        const message = errors?.[fileName]?.[0] ?? errors?._request?.[0] ?? body?.message;

        if (typeof message === 'string') {
            return message;
        }
        if (status === 403 || status === 419) {
            return 'Request rejected. Your session may have expired — reload the page and try again.';
        }

        return `Server returned status ${status}.`;
    };

    const sendFile = (form, { url, field, token, list }, file) =>
        new Promise((resolve) => {
            const item = createItem(list, file.name);
            const data = new FormData();
            data.append(field, file, file.name);
            if (token) {
                data.append('_token', token);
            }

            const fail = (message, response = null) => {
                item.finish('error', message);
                emit(form, 'error', { file, message, response });
                resolve({ file, ok: false, response });
            };

            const xhr = new XMLHttpRequest();
            xhr.open('POST', url);
            xhr.setRequestHeader('Accept', 'application/json');
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            xhr.upload.addEventListener('progress', (event) => {
                if (event.lengthComputable) {
                    item.progress((event.loaded / event.total) * 100);
                }
            });

            xhr.addEventListener('load', () => {
                let body = null;
                try {
                    body = JSON.parse(xhr.responseText);
                } catch {
                    // Non-JSON response; handled below via status code.
                }

                if (xhr.status >= 200 && xhr.status < 300) {
                    item.finish('success', 'Upload successful');
                    emit(form, 'success', { file, response: body });
                    resolve({ file, ok: true, response: body });
                } else {
                    fail(errorMessage(xhr.status, body, file.name), body);
                }
            });

            xhr.addEventListener('error', () => fail('Upload failed. A network error occurred.'));
            xhr.addEventListener('timeout', () => fail('Upload timed out.'));

            xhr.send(data);
        });

    const init = (form) => {
        const url = form.dataset.rhapsodyUpload;
        const input = form.querySelector('input[type="file"]');

        if (!url || !input) {
            console.warn('[rhapsody-uploader] Form needs a data-rhapsody-upload URL and a file input.', form);
            return;
        }

        let list = form.querySelector('[data-upload-list]');
        if (!list) {
            list = document.createElement('ul');
            list.dataset.uploadList = '';
            form.append(list);
        }
        list.setAttribute('aria-live', 'polite');

        form.addEventListener('submit', async (event) => {
            event.preventDefault();

            const files = Array.from(input.files ?? []);
            if (files.length === 0 || form.dataset.uploading === 'true') {
                return;
            }

            const submit = form.querySelector('[type="submit"]');
            const options = {
                url,
                field: input.name,
                token: form.querySelector('input[name="_token"]')?.value ?? '',
                list,
            };

            form.dataset.uploading = 'true';
            list.setAttribute('aria-busy', 'true');
            if (submit) {
                submit.disabled = true;
            }
            list.replaceChildren();
            emit(form, 'start', { files });

            const results = await Promise.all(files.map((file) => sendFile(form, options, file)));

            delete form.dataset.uploading;
            list.setAttribute('aria-busy', 'false');
            if (submit) {
                submit.disabled = false;
            }
            emit(form, 'complete', { results });
        });
    };

    const boot = () => document.querySelectorAll('form[data-rhapsody-upload]').forEach(init);

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
