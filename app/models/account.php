<?php
require_once "money.php";
class Account extends \simp\Model
{
    public $users;
    public $new_user;
    public $starting_balance;
    public $current_balance;

    public function Setup()
    {
        $this->users = array();
        $this->starting_balance = 0;
        $this->current_balance = 0;
    }

    public function OnLoad()
    {
        $user_beans = \R::related($this->_bean, 'user');
        foreach ($user_beans as $id => $bean)
        {
            $this->users[$id] = new User($bean);
        }
        if ($this->id > 0)
        {
            $this->starting_balance = ToDollars($this->start_balance);
            $this->current_balance = ToDollars($this->balance);
        }
    }

    public function BeforeSave()
    {
        $this->start_balance = FromDollars($this->starting_balance);
        if ($this->id == 0)
        {
            $this->balance = $this->start_balance;
        }
        return true;
    }

    public function BeforeDelete()
    {
        \R::clearRelations($this->_bean, 'user');
        return true;
    }

    public function AddUser($user_id)
    {
        if ($this->id > 0)
        {
            $user = \simp\Model::FindById('User', $user_id);
            \R::associate($this->_bean, $user->Bean());
            $this->users[$user_id] = $user;
        }
    }

    public function RemoveUser($user_id)
    {
        if ($this->id > 0)
        {
            $user = \simp\Model::FindById('User', $user_id);
            \R::unassociate($this->_bean, $user->Bean());
            unset($this->users[$user_id]);
        }
    }

    public static function GetTeamTransactions($team_id, $start_date = 0, $end_date = PHP_INT_MAX)
    {
        $txns = \simp\Model::Find(
            "Transaction", 
            "team_id = ? and date >= ? and date <= ?", 
            array($team_id, $start_date, $end_date)
        );
        return $txns;
    }

    public function Recalculate()
    {
        // load all transactions for this account and recompute balance
        $txns = \simp\Model::Find("Txn", "account_id = ? order by timestamp asc", array($this->id));
        $b = $this->start_balance;
        foreach ($txns as $t)
        {
            $b += $t->value;
        }
        $this->balance = $b;
        $this->Save();
    }
}
