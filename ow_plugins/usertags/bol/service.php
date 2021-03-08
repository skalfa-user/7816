<?php

/**
 *
 */
final class USERTAGS_BOL_Service {
	/**
	 *
	 */
        private $userDao;

	/**
	 *
	 */
	protected static $instance = null;

    private function __construct()
    {
        $this->userDao = USERTAGS_BOL_UserDao::getInstance();
    }

	public static function getInstance() {
		if ( self::$instance==null ) {
			self::$instance = new USERTAGS_BOL_Service();
		}

		return self::$instance;
	}


    public function getUserListData( $first, $count)
    {
	return;
        $number = 0;
        $users = array();

        if (isset($_GET['tag'])) { 
          $number = $this->countUsersByTags($_GET['tag']);
          $users = $this->findUsersByTags($first, $count, $_GET['tag']);
        } 
          
        return array(
		$users, 
		$number
          );

    }

    public function countUsersByTags( $tag ) {
	$tagservice = BOL_TagService::getInstance();
	return $tagservice->findEntityCountByTag('usertags', $tag);
    }

    public function findUsersByTags( $first, $limit, $tag ) {
        $idList = BOL_TagService::getInstance()->findEntityListByTag('usertags', $tag, $first, $limit);

	if (count($idList) > 0) {
          $dtoList = $this->userDao->findListByIdList($idList);
          return $dtoList;
        } else 
	return array();
    }

}
