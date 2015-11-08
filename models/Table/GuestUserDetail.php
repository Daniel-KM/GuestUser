<?php
/**
 * The table for Guest User Details.
 */
class Table_GuestUserDetail extends Omeka_Db_Table
{
    protected $_target = 'GuestUserDetail';

    /**
     * Get the object for the specified user.
     *
     * @param User|integer $user
     * @return GuestUserDetail|null
     */
    public function findByUser($user)
    {
        $params = array();
        $params['user_id'] = (integer) (is_object($user) ? $user->id : $user);
        $result = $this->findBy($params, 1);
        return $result ? reset($result) : null;
    }
}
