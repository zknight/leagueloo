<?php
namespace app\admin;
class TransactionController extends \simp\Controller
{
    function Setup()
    {
        $this->SetLayout("admin");
        $this->RequireAuthorization(
            array(
                //'index',
                //'show',
                'add',
                'edit',
                'delete',
            )
        );

        $this->MapAction("add", "Create", \simp\Request::POST);
        $this->MapAction("edit", "Update", \simp\Request::PUT);
        $this->MapAction("delete", "Remove", \simp\Request::DELETE);
    }

    public function Add()
    {
        $account_id = $this->GetParam('id');
        $this->account = \simp\Model::FindById("Account", $account_id);
        $type = \Affiliation::GetType($this->GetUser()->id, $this->account->team_id);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            $this->transaction = \simp\Model::Create("Txn");
            $this->transaction->timestamp = time();
        }
        else
        {
            AddFlash("You do not have privileges for this action.");
            \Redirect(\GetReturnURL());
        }
        return true;
    }

    public function Create()
    {
        $this->transaction = \simp\Model::Create("Txn");
        $vars = $this->GetFormVariable('Txn');
        $team_id = $vars['team_id'];
        $type = \Affiliation::GetType($this->GetUser()->id, $team_id);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            $this->transaction->UpdateFromArray($vars);
            $this->account = \simp\Model::FindById("Account", $vars['account_id']);
            if (!$this->transaction->Save())
            {
                $this->SetAction('add');
            }
            else
            {
                // recalculate account
                $this->account->Recalculate();
                AddFlash("Transaction added.");
                \Redirect(\GetReturnURL());
            }
        }
        else
        {
            AddFlash("You do not have privileges for this action.");
            \Redirect(\GetReturnURL());
        }
        return true;
    }

    public function Edit()
    {
        $txn_id = $this->GetParam('id');
        $this->transaction = \simp\Model::FindById("Txn", $txn_id);
        $team_id = $this->transaction->team_id;
        $type = \Affiliation::GetType($this->GetUser()->id, $team_id);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            $this->account = \simp\Model::FindById("Account", $this->transaction->account_id);
            return true;
        }
        else
        {
            AddFlash("You do not have privileges for this action.");
            \Redirect(\GetReturnURL());
        }
    }

    public function Update()
    {
        $this->transaction = \simp\Model::FindById('Txn', $this->GetParam('id'));
        $vars = $this->GetFormVariable('Txn');
        $team_id = $vars['team_id'];
        $type = \Affiliation::GetType($this->GetUser()->id, $team_id);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            $this->transaction->UpdateFromArray($vars);
            $this->account = \simp\Model::FindById("Account", $vars['account_id']);
            if (!$this->transaction->Save())
            {
                $this->SetAction('edit');
            }
            else
            {
                // recalculate account
                $this->account->Recalculate();
                AddFlash("Transaction updated.");
                \Redirect(\GetReturnURL());
            }
        }
        else
        {
            AddFlash("You do not have privileges for this action.");
            \Redirect(\GetReturnURL());
        }
        return true;
    }

    public function Remove()
    {
        $this->transaction = \simp\Model::FindById('Txn', $this->GetParam('id'));
        $team_id = $this->transaction->team_id;
        $type = \Affiliation::GetType($this->GetUser()->id, $team_id);
        if ($type == \Affiliation::TREASURER || $type == \Affiliation::MANAGER)
        {
            $this->account = \simp\Model::FindById("Account", $this->transaction->account_id);
            $this->transaction->Delete();
            $this->account->Recalculate();
            AddFlash("Transaction deleted.");
            \Redirect(\GetReturnURL());
        }
        else
        {
            AddFlash("You do not have privileges for this action.");
            \Redirect(\GetReturnURL());
        }
    }
}
