<?php
/**
 * The table for Guest User Details.
 */
class Table_GuestUserDetail extends Omeka_Db_Table
{
    protected $_target = 'GuestUserDetail';

    /**
     * Get the details for the specified user or list of user.
     *
     * @param User|array|integer $users One or multiple user.
     * @return GuestUserDetail|array|null Return an associative array when
     * multiple user are requested.
     */
    public function findByUsers($users)
    {
        $params = array();

        if (!is_array($users)) {
            $one = true;
            $users = array($users);
        }
        // Multiple users.
        else {
            $one = false;
        }

        $params['user_id'] = array_map(
            function ($value) {
                 return (integer) ((is_object($value)) ? $value->id : $value);
            },
            $users);

        $result = $this->findBy($params);
        if ($one) {
            return $result ? reset($result) : null;
        }

        // Return details by user.
        $details = array();
        foreach ($result as $value) {
            $details[$value->user_id] = $value;
        }
        return $details;
    }
}
