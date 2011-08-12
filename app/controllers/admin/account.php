<?php
namespace app\admin;
class AccountController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            array(
                'index',
                'show',
                'add',
                'edit',
                'delete',
                'adduser',
                'deluser'
            )
        );

        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);
    }

    function Index()
    {
        // check to see if user has affiliation as team manager or treasurer of team
        global $log;
        $has_access = false;
        $this->user = $this->GetUser();
        $this->team_account_info = array();
        $affiliations = \simp\Model::Find("Affiliation", "user_id = ?", array($this->user->id));
        foreach ($affiliations as $aff)
        {
            $log->logDebug("Account::Index() aff = " . print_r($aff, true));
            if (($aff->type == \Affiliation::MANAGER || $aff->type == \Affiliation::TREASURER) 
                &&
                $aff->confirmed)
            {
                $has_access = true;
                // TODO: make this by month?  or by season?
                //\R::debug(true);
                $this->team_account_info[] = array(
                    'team_id' => $aff->team_id,
                    'team_name' => $aff->team_name,
                    'accounts' => \simp\Model::Find("Account", "team_id = ? order by last_name asc", array($aff->team_id)),
                    /*
                    'transactions' => \Account::GetTeamTransactions(
                        $aff->team_id)
                     */
                    );
                //\R::debug(false);
            }
        }

        if (!$has_access)
        {
            AddFlash("You do not have privileges for this action.");
            \Redirect(\GetReturnURL());
        }
        $this->StoreLocation();
        return true;
    }

    function Show()
    {
        $id = $this->GetParam('id');
        $this->account = \simp\Model::FindById("Account", $id);
        $team_id = $this->account->team_id;
        $type = \Affiliation::GetType($this->GetUser()->id, $team_id);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            \R::debug(true);
            $this->transactions = \simp\Model::Find(
                "Txn",
                "account_id = ? order by timestamp asc",
                array($this->account->id)
            );
            \R::debug(false);
            $this->users = \Affiliation::GetUsers($team_id);
            $this->StoreLocation();
            return true;
        }
        else
        {
            AddFlash("You do not have privileges for this action.");
            \Redirect(\GetReturnURL());
        }
    }

    function Add()
    {
        $team_id = $this->GetParam('id');
        $type = \Affiliation::GetType($this->GetUser()->id, $team_id);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            $this->team = \simp\Model::FindById("Team", $team_id);
            // TODO: make this another action to add multiple users
            //$this->users = \Affiliation::GetUsers($team_id);
            $this->account = \simp\Model::Create("Account");
            return true;
        }
        else
        {
            AddFlash("You do not have sufficient privilege to do that.");
            \Redirect(\GetReturnURL());
        }
    }

    function Create()
    {
        $vars = $this->GetFormVariable('Account');
        //$vars['current_balance'] = $vars['starting_balance'];
        $type = \Affiliation::GetType($this->GetUser()->id, $vars['team_id']);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            $this->account = \simp\Model::Create("Account");
            $this->account->UpdateFromArray($vars);
            if ($this->account->Save())
            {
                AddFlash("Account for {$this->account->first_name} {$this->account->last_name} created.");
                \Redirect(\GetReturnURL());
            }
            else
            {
                $this->team = \simp\Model::FindById("Team", $vars['team_id']);
                $this->SetAction('add');
            }

        }
        else 
        {
            AddFlash("You do not have sufficient privilege to do that.");
            \Redirect(\GetReturnURL());
        }
        return true;
    }

    function Edit()
    {
        $id = $this->GetParam('id');
        $this->account = \simp\Model::FindById('Account', $id);
        $team_id = $this->account->team_id;
        $type = \Affiliation::GetType($this->GetUser()->id, $team_id);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            $this->team = \simp\Model::FindById("Team", $team_id);
        }
        else
        {
            AddFlash("You do not have sufficient privilege to do that.");
            \Redirect(\GetReturnURL());
        }

        return true;
    }

    function Update()
    {
        // need to recalculate balance
        return false;
    }

    function Remove()
    {
        $id = $this->GetParam('id');
        $this->account = \simp\Model::FindById('Account', $id);
        $team_id = $this->account->team_id;
        $type = \Affiliation::GetType($this->GetUser()->id, $team_id);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            $this->account->Delete();
            AddFlash("Account deleted.");
        }
        else
        {
            AddFlash("You do not have sufficient privilege to do that.");
        }
        \Redirect(\GetReturnURL());
    }

    function Adduser()
    {
        $account_id = $this->GetParam('id');
        $this->account = \simp\Model::FindById("Account", $account_id);
        $team_id = $this->account->team_id;
        $type = \Affiliation::GetType($this->GetUser()->id, $team_id);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            $vars = $this->GetFormVariable('Account');
            $user_id = $vars['new_user'];
            $this->account->AddUser($user_id);
            AddFlash("User added.");
        }
        else
        {
            AddFlash("You do not have sufficient privilege to do that.");
        }
        \Redirect(\GetReturnURL());
    }

    function Deluser()
    {
        $account_id = $this->GetParam('id');
        $user_id = $this->GetParam('u');
        $this->account = \simp\Model::FindById("Account", $account_id);
        $team_id = $this->account->team_id;
        $type = \Affiliation::GetType($this->GetUser()->id, $team_id);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            $this->account->RemoveUser($user_id);
            AddFlash("User removed.");
        }
        else
        {
            AddFlash("You do not have sufficient privilege to do that.");
        }
        \Redirect(\GetReturnURL());
    }

}
