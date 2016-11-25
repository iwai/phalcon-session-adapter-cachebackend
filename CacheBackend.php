<?php
 /**
  * CacheBackend.php
  *
  * @copyright   Copyright (c) 2013 Yuji Iwai.
  * @package     
  * @subpackage  
  * @version     $Id$
  */

namespace Iwai\Phalcon\Session\Adapter;

use Phalcon\Session\Adapter as BaseAdapter;
use Phalcon\Session\AdapterInterface;

class CacheBackend extends BaseAdapter implements AdapterInterface {

    /** @var \Phalcon\Cache\BackendInterface $backend */
    protected $backend = null;

    protected $cycle_time;

    protected $read_cache = array();

    public function __construct($options = null)
    {
        if (isset($options['cycle_time']))
            $this->cycle_time = intval($options['cycle_time']);

        session_set_save_handler(
            array($this, '_open'),
            array($this, '_close'),
            array($this, '_read'),
            array($this, '_write'),
            array($this, '_destroy'),
            array($this, '_gc')
        );
        register_shutdown_function('session_write_close');

        parent::__construct($options);
    }

    /**
     * @param $backend
     * @return $this
     */
    public function setBackend($backend)
    {
        $this->backend = $backend;
        return $this;
    }

    /**
     * @return \Phalcon\Cache\BackendInterface
     */
    public function getBackend()
    {
        return $this->backend;
    }

    /**
     * Session open handler
     *
     * @param   string $savePath       Save path
     * @param   string $sessionName    Session name
     * @throws  \Exception
     * @return  bool
     */
    public function _open($savePath, $sessionName)
    {
        if (!( $this->backend instanceof \Phalcon\Cache\BackendInterface )
            && !( $this->backend instanceof \Phalcon\Cache\Multiple ))
            throw new \Exception('Required cache backend instance');

        return true;
    }

    /**
     * Session close handler
     *
     * @return  bool
     */
    public function _close()
    {
        return true;
    }

    /**
     * Read session handler
     *
     * @param   string  $id       Session id
     * @return  string
     */
    public function _read($id)
    {
        return $this->read_cache[$id] = (string)$this->backend->get($id);
    }

    /**
     * Write session handler
     *
     * @param   string  $id       Session id
     * @param   string  $data     Data
     * @return  bool
     * */
    public function _write($id, $data)
    {
        if ($data === '')
            return true;

        if ($this->inCycleTime($id, $data))
            return true;

        $_SESSION['_last_updated_at'] = time();

        $this->backend->save($id, session_encode());

        return true;
    }

    /**
     * Destroy session handler
     *
     * @param   string  $id       Session id
     * @return  bool
     */
    public function _destroy($id)
    {
        $this->backend->delete($id);

        return true;
    }

    /**
     * Garbage collect
     *
     * @return  bool
     * */
    public function _gc()
    {
        return true;
    }

    protected function inCycleTime($id, $data)
    {
        if (!isset($this->cycle_time))
            return false;
        if (!isset($this->read_cache[$id]))
            return false;
        if ($this->read_cache[$id] !== $data)
            return false;

        if (!isset($_SESSION['_last_updated_at']))
            return false;
        if ((time() - $_SESSION['_last_updated_at']) > $this->cycle_time)
            return false;

        return true;
    }
}
