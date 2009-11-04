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

        if (file_exists($this->directory)) {
            $cleaner = new Stagehand_DirectoryCleaner();
            $cleaner->clean($this->directory);
            rmdir($this->directory);
        }

        mkdir($this->directory);
    }

    public function tearDown() { }

    /**
     * @test
     */
    public function memorizeAndReproduceDirectory()
    {
        $this->createTestFiles();

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

        $this->assertFileExists($this->directory . '/example.txt');
        $this->assertEquals(file_get_contents($this->directory . '/example.txt'),
                            'example'
                            );

        $this->assertFileExists($this->directory . '/path');
        $this->assertTrue(is_dir($this->directory . '/path'));
        $this->assertFileExists($this->directory . '/path/foo.txt');
        $this->assertFileExists($this->directory . '/path/bar.txt');
        $this->assertEquals(file_get_contents($this->directory . '/path/foo.txt'),
                            'foo file'
                            );
        $this->assertEquals(file_get_contents($this->directory . '/path/bar.txt'),
                            'bar file'
                            );

        $this->assertFileExists($this->directory . '/path/to');
        $this->assertTrue(is_dir($this->directory . '/path/to'));

        $this->assertFileExists($this->directory . '/path/to/baz.txt');
        $this->assertEquals(file_get_contents($this->directory . '/path/to/baz.txt'),
                            'baz file'
                            );

        $this->assertTrue(is_link($this->directory . '/path/to/qux.txt'));
        $this->assertEquals(file_get_contents($this->directory . '/path/to/qux.txt'),
                            'example'
                            );
    }

    /**
     * @test
     */
    public function reserveDirectoryRebirthByShutdownStep()
    {
        $rebirth = new Stagehand_DirectoryRebirth();
        $rebirth->memorize($this->directory);
        $rebirth->reserve();

        $this->createTestFiles();
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected function createTestFiles()
    {
        touch($this->directory . '/example.txt');
        file_put_contents($this->directory . '/example.txt', 'example');

        mkdir($this->directory . '/path');
        touch($this->directory . '/path/foo.txt');
        touch($this->directory . '/path/bar.txt');
        file_put_contents($this->directory . '/path/foo.txt', 'foo file');
        file_put_contents($this->directory . '/path/bar.txt', 'bar file');

        mkdir($this->directory . '/path/to');
        touch($this->directory . '/path/to/baz.txt');
        file_put_contents($this->directory . '/path/to/baz.txt', 'baz file');

        symlink($this->directory . '/example.txt',
                $this->directory . '/path/to/qux.txt'
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
