// marquee-init.js

document.addEventListener('DOMContentLoaded', function () {
  if (typeof InfiniteMarquee === 'undefined') {
    console.error('InfiniteMarquee is not loaded.');
    return;
  }

  const animation = {
    infiniteLeft() {
      if (document.querySelector('.logos-marquee-container')) {
        new InfiniteMarquee({
          element: '.logos-marquee-container',
          speed: 55000,
          smoothEdges: true,
          direction: 'left',
          gap: '32px',
          duplicateCount: 1,
          mobileSettings: {
            direction: 'top',
            speed: 50000,
          },
          on: {
            beforeInit: () => console.log('Not Yet Initialized'),
            afterInit: () => console.log('Initialized'),
          },
        });
      }
    },

    infiniteRight() {
      if (document.querySelector('.logos-right-marquee-container')) {
        new InfiniteMarquee({
          element: '.logos-right-marquee-container',
          speed: 55000,
          smoothEdges: true,
          direction: 'right',
          gap: '32px',
          duplicateCount: 1,
          mobileSettings: {
            direction: 'right',
            speed: 50000,
          },
          on: {
            beforeInit: () => console.log('Not Yet Initialized'),
            afterInit: () => console.log('Initialized'),
          },
        });
      }
    },

    initHover() {
      if (document.querySelector('.cards-marquee-container')) {
        new InfiniteMarquee({
          element: '.cards-marquee-container',
          speed: 140000,
          smoothEdges: true,
          direction: 'left',
          gap: '32px',
          pauseOnHover: true,
          on: {
            beforeInit: () => console.log('Not Yet Initialized'),
            afterInit: () => console.log('Initialized'),
          },
        });
      }
    },

    initHoverRight() {
      if (document.querySelector('.cards-right-marquee-container')) {
        new InfiniteMarquee({
          element: '.cards-right-marquee-container',
          speed: 140000,
          smoothEdges: true,
          direction: 'right',
          gap: '32px',
          pauseOnHover: true,
          on: {
            beforeInit: () => console.log('Not Yet Initialized'),
            afterInit: () => console.log('Initialized'),
          },
        });
      }
    },
   
  };

  // Initialize all
  animation.infiniteLeft();
  animation.infiniteRight();
  animation.initHover();
  animation.initHoverRight();
});
