<?php

class USERTAGS_BOL_UserDao extends OW_BaseDao
{
    /**
     * @var BOL_UserDao
     */
    private $userDao;
    /**
     * Singleton instance.
     *
     * @var BOL_UserDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return BOL_UserDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();
        $this->userDao = BOL_UserDao::getInstance();
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return $this->userDao->getDtoClassName();
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return $this->userDao->getTableName();
    }

    public function findListByIdList( $list)
    {
        $ex = new OW_Example();

        $ex->andFieldInArray('id', $list);
//        $ex->andFieldEqual('privacy', 'everybody');

        $ex->setOrder('id DESC');

        return $this->findListByExample($ex);
    }

}