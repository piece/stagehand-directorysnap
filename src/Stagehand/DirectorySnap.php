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
 * @package    Stagehand_DirectorySnap
 * @copyright  2009 mbarracuda <mbarracuda@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      File available since Release 0.1.0
 */

// {{{ Stagehand_DirectorySnap

/**
 * Stagehand_DirectorySnap
 *
 * @package    Stagehand_DirectorySnap
 * @copyright  2009 mbarracuda <mbarracuda@gmail.com>
 * @license    http://www.opensource.org/licenses/bsd-license.php  BSD License (revised)
 * @version    Release: @package_version@
 * @since      Class available since Release 0.1.0
 */
class Stagehand_DirectorySnap
{

    // {{{ properties

    /**#@+
     * @access public
     */

    /**#@-*/

    /**#@+
     * @access protected
     */

    protected $snappedPath;
    protected $elements = array();
    protected $useTemporary = false;
    protected $temporaryPath;

    /**#@-*/

    /**#@+
     * @access private
     */

    /**#@-*/

    /**#@+
     * @access public
     */

    // }}}
    // {{{ snap()

    /**
     * @param string $path
     */
    public function snap($path)
    {
        $this->snappedPath = $path;

        if ($this->useTemporary) {
            $callback = array($this, 'pushToTemporary');
        } else {
            $callback = array($this, 'collectElements');
        }

        $scanner = new Stagehand_DirectoryScanner($callback, false);
        $scanner->scan($this->snappedPath);
    }

    // }}}
    // {{{ restore()

    /**
     * @throws Stagehand_DirectorySnap_Exception Snap a directory first.
     */
    public function restore()
    {
        if (!$this->snappedPath) {
            throw new Stagehand_DirectorySnap_Exception('Snap a directory first.');
        }

        $cleaner = new Stagehand_DirectoryCleaner();
        $cleaner->clean($this->snappedPath);
        
        if ($this->useTemporary) {
            $scanner = new Stagehand_DirectoryScanner(array($this, 'restoreOrigin'), false);
            $scanner->scan($this->temporaryPath);
        } else {
            foreach ($this->elements as $element) {
                $element->push($this->snappedPath);
            }
        }
    }

    // }}}
    // {{{ reserve()

    /**
     * @throws Stagehand_DirectorySnap_Exception Snap a directory first.
     */
    public function reserve()
    {
        if (!$this->snappedPath) {
            throw new Stagehand_DirectorySnap_Exception('Snap a directory first.');
        }

        register_shutdown_function(array($this, 'restore'));
    }

    // }}}
    // {{{ useTemporary()

    /**
     * @param string $temporaryPath
     */
    public function useTemporary($temporaryPath)
    {
        $this->useTemporary = true;
        $this->temporaryPath = $temporaryPath;
    }

    // }}}
    // {{{ collectElements()

    /**
     * @param string $filePath
     */
    public function collectElements($filePath)
    {
        $this->elements[] =
            Stagehand_DirectorySnap_Element_Factory::factory($filePath);
    }

    // }}}
    // {{{ pushToTemporary()

    /**
     * @param string $filePath
     */
    public function pushToTemporary($filePath)
    {
        $element = Stagehand_DirectorySnap_Element_Factory::factory($filePath);
        $element->push($this->temporaryPath, $this->snappedPath);
    }

    // }}}
    // {{{ restoreOrigin()

    /**
     * @param string $filePath
     */
    public function restoreOrigin($filePath)
    {
        $element = Stagehand_DirectorySnap_Element_Factory::factory($filePath);
        $element->push($this->snappedPath, $this->temporaryPath);
    }

    /**#@-*/

    /**#@+
     * @access protected
     */

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
