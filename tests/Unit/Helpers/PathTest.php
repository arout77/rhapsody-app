<?php
namespace Tests\Unit\Helpers;

use Rhapsody\Core\Helpers\Path;
use Tests\TestCase;

class PathTest extends TestCase
{
    public function test_it_resolves_root_path_correctly()
    {
        $expected = ROOT_DIR . DIRECTORY_SEPARATOR . 'config';

        $this->assertEquals($expected, Path::root('config'));
    }

    public function test_it_resolves_storage_path_correctly()
    {
        $expected = ROOT_DIR . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'cache';

        $this->assertEquals($expected, Path::storage('cache'));
    }
}
