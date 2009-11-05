<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * PHP version 5
 *
 * Copyright (c) 2009 mbarracuda <mbarracuda@gmail.com>,
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package    Stagehand_DirectoryRebirth
 * @copyright  2009 mbarracuda <mbarracuda@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

// {{{ Stagehand_PHP_DirectoryRebirthTest

/**
 * Some tests for Stagehand_DirectoryRebirth
 *
 * @package    Stagehand_DirectoryRebirth
 * @copyright  2009 mbarracuda <mbarracuda@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Stagehand_DirectoryRebirthTest extends PHPUnit_Framework_TestCase
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $directory;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    public function setUp()
    {
        $this->directory = dirname(__FILE__) . '/DirectoryRebirthTest';
        $this->temporary = dirname(__FILE__) . '/temporary';

        $this->resetDirectory($this->directory);
        $this->resetDirectory($this->temporary);
    }

    public function tearDown() { }

    /**
     * @test
     */
    public function memorizeAndReproduceDirectory()
    {
        $this->createTestFiles($this->directory);

        $rebirth = new Stagehand_DirectoryRebirth();
        $rebirth->memorize($this->directory);

        $cleaner = new Stagehand_DirectoryCleaner();
        $cleaner->clean($this->directory);

        $this->assertFileNotExists($this->directory . '/example.txt');
        $this->assertFileNotExists($this->directory . '/path');
        $this->assertFileNotExists($this->directory . '/path/foo.txt');
        $this->assertFileNotExists($this->directory . '/path/bar.txt');
        $this->assertFileNotExists($this->directory . '/path/to');
        $this->assertFileNotExists($this->directory . '/path/to/baz.txt');
        $this->assertFileNotExists($this->directory . '/path/to/qux.txt');

        $rebirth->reproduce();

        $this->assertTestFileExists($this->directory);
    }

    /**
     * @test
     */
    public function useTemporary()
    {
        $this->createTestFiles($this->directory);

        $rebirth = new Stagehand_DirectoryRebirth();
        $rebirth->useTemporary($this->temporary);
        $rebirth->memorize($this->directory);

        $this->assertTestFileExists($this->temporary);

        $cleaner = new Stagehand_DirectoryCleaner();
        $cleaner->clean($this->directory);

        $this->assertFileNotExists($this->directory . '/example.txt');
        $this->assertFileNotExists($this->directory . '/path');
        $this->assertFileNotExists($this->directory . '/path/foo.txt');
        $this->assertFileNotExists($this->directory . '/path/bar.txt');
        $this->assertFileNotExists($this->directory . '/path/to');
        $this->assertFileNotExists($this->directory . '/path/to/baz.txt');
        $this->assertFileNotExists($this->directory . '/path/to/qux.txt');

        $rebirth->reproduce();

        $this->assertTestFileExists($this->directory);
    }

    /**
     * @test
     */
    public function reserveDirectoryRebirthByShutdownStep()
    {
        $rebirth = new Stagehand_DirectoryRebirth();
        $rebirth->memorize($this->directory);
        $rebirth->reserve();

        $this->createTestFiles($this->directory);
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected function resetDirectory($path)
    {
        if (file_exists($path)) {
            $cleaner = new Stagehand_DirectoryCleaner();
            $cleaner->clean($path);
        } else {
            mkdir($path);
        }
    }

    protected function createTestFiles($path)
    {
        touch($path . '/example.txt');
        file_put_contents($path . '/example.txt', 'example');

        mkdir($path . '/path');
        touch($path . '/path/foo.txt');
        touch($path . '/path/bar.txt');
        file_put_contents($path . '/path/foo.txt', 'foo file');
        file_put_contents($path . '/path/bar.txt', 'bar file');

        mkdir($path . '/path/to');
        touch($path . '/path/to/baz.txt');
        file_put_contents($path . '/path/to/baz.txt', 'baz file');

        symlink($path . '/example.txt',
                $path . '/path/to/qux.txt'
                );
    }

    protected function assertTestFileExists($path)
    {
        $this->assertFileExists($path . '/example.txt');
        $this->assertEquals(file_get_contents($path . '/example.txt'),
                            'example'
                            );

        $this->assertFileExists($path . '/path');
        $this->assertTrue(is_dir($path . '/path'));
        $this->assertFileExists($path . '/path/foo.txt');
        $this->assertFileExists($path . '/path/bar.txt');
        $this->assertEquals(file_get_contents($path . '/path/foo.txt'),
                            'foo file'
                            );
        $this->assertEquals(file_get_contents($path . '/path/bar.txt'),
                            'bar file'
                            );

        $this->assertFileExists($path . '/path/to');
        $this->assertTrue(is_dir($path . '/path/to'));

        $this->assertFileExists($path . '/path/to/baz.txt');
        $this->assertEquals(file_get_contents($path . '/path/to/baz.txt'),
                            'baz file'
                            );

        $this->assertTrue(is_link($path . '/path/to/qux.txt'));
        $this->assertEquals(file_get_contents($path . '/path/to/qux.txt'),
                            'example'
                            );
    }

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    // }}}
}

// }}}

/*
 * Local Variables:
 * mode: php
 * coding: iso-8859-1
 * tab-width: 4
 * c-basic-offset: 4
 * c-hanging-comment-ender-p: nil
 * indent-tabs-mode: nil
 * End:
 */
