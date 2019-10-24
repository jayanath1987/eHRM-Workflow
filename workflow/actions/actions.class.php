<?php

/**
 * OrangeHRM is a comprehensive Human Resource Management (HRM) System that captures
 * all the essential functionalities required for any enterprise.
 * Copyright (C) 2006 OrangeHRM Inc., http://www.orangehrm.com
 *
 * OrangeHRM is free software; you can redistribute it and/or modify it under the terms of
 * the GNU General Public License as published by the Free Software Foundation; either
 * version 2 of the License, or (at your option) any later version.
 *
 * OrangeHRM is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program;
 * if not, write to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA  02110-1301, USA
 */
/**
 * Actions class for Workflow module
 *
 *-------------------------------------------------------------------------------------------------------
 *  Author    - Jayanath Liyanage
 *  On (Date) - 27 July 2011 
 *  Comments  - Employee workflow main Function
 *  Version   - Version 1.0
 * -------------------------------------------------------------------------------------------------------
**/

include ('../../lib/common/LocaleUtil.php');

class workflowActions extends sfActions {

    /**
     * Executes index action
     *
     * @param sfRequest $request A request object
     */
    public function executeIndex(sfWebRequest $request) {
        $this->forward('default', 'module');
    }
    
    public function executeAjaxEncryption(sfWebRequest $request) {

        $wfId = $request->getParameter('wfId');
        $encrypt = new EncryptionHandler();
        echo json_encode($encrypt->encrypt($wfId));
        die;
    }

    public function executeApprovalGroupsSummary(sfWebRequest $request) {
        try {
            $this->culture = $this->getUser()->getCulture();


            $wfDao = new workflowDao();


            $this->sorter = new ListSorter('wf', 'wf', $this->getUser(), array('wfappgrp_code', ListSorter::ASCENDING));

            $this->sorter->setSort(array($request->getParameter('sort'), $request->getParameter('order')));

//            if ($request->getParameter('mode') == 'search') {
//                if ($request->getParameter('searchMode') != 'all' && trim($request->getParameter('searchValue')) == '') {
//                    $this->setMessage('NOTICE', array('Select the field to search'));
//                    $this->redirect('default/error');
//                }
//            }
            $this->searchMode = ($request->getParameter('searchMode') == '') ? 'all' : $request->getParameter('searchMode');
            $this->searchValue = ($request->getParameter('searchValue') == '') ? '' : $request->getParameter('searchValue');

            $this->sort = ($request->getParameter('sort') == '') ? 'ag.wfappgrp_code' : $request->getParameter('sort');
            $this->order = ($request->getParameter('order') == '') ? 'DESC' : $request->getParameter('order');

            $res = $wfDao->searchApprovalGroups($this->searchMode, $this->searchValue, $this->culture, $request->getParameter('page'), $this->sort, $this->order);
            $this->AppGroupList = $res['data'];
            //die($this->trainSummeryList);
            $this->pglay = $res['pglay'];
            $this->pglay->setTemplate('<a href="{%url}">{%page}</a>');
            $this->pglay->setSelectedTemplate('{%page}');
            
         } catch (sfStopException $sf) {
             
        }   catch (Exception $e) {
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('default/error');
        }
        catch (Doctrine_Connection_Exception $e) {

            $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('default/error');
        } catch (sfStopException $sf) {
            $this->redirect('default/error');
        }
    }

    public function executeSaveAppGroup(sfWebRequest $request) {


        $wfDao = new workflowDao();

        if (!strlen($request->getParameter('lock'))) {
            $this->mode = 0;
        } else {
            $this->mode = $request->getParameter('lock');
        }
        $ebLockid = $request->getParameter('id'); //die($transPid);
        // die($this->lockMode);
        if (isset($this->mode)) {
            if ($this->mode == 1) {

                $conHandler = new ConcurrencyHandler();

                $recordLocked = $conHandler->setTableLock('hs_hr_wf_approval_group', array($ebLockid), 1);

                if ($recordLocked) {
                    // Display page in edit mode
                    $this->mode = 1;
                } else {

                    $this->mode = 0;
                    $this->setMessage('WARNING', array($this->getContext()->getI18N()->__('Can not update. Record locked by another user.', $args, 'messages')), true);

                    $this->redirect('workflow/approvalGroupsSummary');
                }
            } else if ($this->mode == 0) {
                $conHandler = new ConcurrencyHandler();
                $conHandler->resetTableLock('hs_hr_wf_approval_group', array($ebLockid), 1);
                $this->mode = 0;
            }
        }
        $requestId = $request->getParameter('id');
        if (strlen($requestId)) {
            if (!strlen($this->mode)) {
                $this->mode = 0;
            }
            $this->wfappgrp = $wfDao->readAppGroup($requestId);
            if (!$this->wfappgrp) {
                $this->setMessage('WARNING', array($this->getContext()->geti18n()->__('Record Not Found')));
                $this->redirect('workflow/approvalGroupsSummary');
            }
        } else {
            $this->mode = 1;
        }
        if ($request->isMethod('post')) {
            $wfGrpDao=new GroupApprovalDao();
            if (strlen($request->getParameter('txtHiddenReqID'))) {

                $wfappgrp = $wfDao->readAppGroup($request->getParameter('txtHiddenReqID'));
            } else {
                $wfappgrp = new WfApprovalGroup();
            }

            $wfappgrp=$wfGrpDao->getWfAppgrpObj($request,$wfappgrp);
            

            $wfappgrp->save();

            if (strlen($requestId)) {
                $this->setMessage('SUCCESS', array($this->getContext()->geti18n()->__('Successfully Updated')));
                $this->redirect('workflow/approvalGroupsSummary');
            } else {

                $this->setMessage('SUCCESS', array($this->getContext()->geti18n()->__('Successfully Added')));
                $this->redirect('workflow/approvalGroupsSummary');
            }
        }
    }

    public function executeDeleteGrpApp(sfWebRequest $request) {

        if (count($request->getParameter('chkLocID')) > 0) {
            $wfDao = new workflowDao();
            try {
                $conn = Doctrine_Manager::getInstance()->connection();
                $conn->beginTransaction();
                $ids = array();
                $ids = $request->getParameter('chkLocID');
                $countArr = array();
                $saveArr = array();
                for ($i = 0; $i < count($ids); $i++) {
                    $conHandler = new ConcurrencyHandler();
                    $isRecordLocked = $conHandler->isTableLocked('hs_hr_wf_approval_group', array($ids[$i]), 1);
                    if ($isRecordLocked) {
                        $countArr = $ids[$i];
                    } else {
                        $saveArr = $ids[$i];
                        $wfDao->deleteGrpApp($ids[$i]);
                        $conHandler->resetTableLock('hs_hr_wf_approval_group', array($ids[$i]), 1);
                    }
                }

                $conn->commit();
            } catch (Doctrine_Connection_Exception $e) {
                $conn->rollBack();
                $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
                $this->setMessage('WARNING', $errMsg->display());
                $this->redirect('workflow/approvalGroupsSummary');
            } catch (Exception $e) {
                $conn->rollBack();
                $errMsg = new CommonException($e->getMessage(), $e->getCode());
                $this->setMessage('WARNING', $errMsg->display());
                $this->redirect('workflow/approvalGroupsSummary');
            }
            if (count($saveArr) > 0 && count($countArr) == 0) {
                $this->setMessage('SUCCESS', array($this->getContext()->getI18N()->__("Successfully Deleted", $args, 'messages')));
            } elseif (count($saveArr) > 0 && count($countArr) > 0) {
                $this->setMessage('WARNING', array($this->getContext()->getI18N()->__("Some records are can not be deleted as them  Locked by another user ", $args, 'messages')));
            } elseif (count($saveArr) == 0 && count($countArr) > 0) {
                $this->setMessage('WARNING', array($this->getContext()->getI18N()->__("Can not delete as them  Locked by another user ", $args, 'messages')));
            }
        } else {
            $this->setMessage('NOTICE', array('Select at least one record to delete'));
        }
        $this->redirect('workflow/approvalGroupsSummary');
    }

    public function executeTestWorkflow(sfWebRequest $request) {
        $wfDao = new workflowDao();
        $wfDao->startWorkFlow(3, 9);
    }

    public function executeApprovalSummary(sfWebRequest $request) {
        try{
        if($_SESSION['user']=="USR001"){
            throw new Exception("Invalid File Type", 200);
                              
        }else{
        $wfDao = new workflowDao(); 
        $approvingEmpID=$_SESSION['empNumber'];
        $this->apSummary = $wfDao->applicationSummary($approvingEmpID);
        $this->culture = $this->getUser()->getCulture();
        }
        }
        catch(sfStopException $sf){
            
        }
//        catch (Doctrine_Connection_Exception $e) {
                
//                $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
//                $this->setMessage('WARNING', $errMsg->display());
//                $this->redirect('default/error');
//            }
             catch (Exception $e) {               
                $errMsg = new CommonException($e->getMessage(), $e->getCode());
                $this->setMessage('WARNING', $errMsg->display());
                $this->redirect('default/error');
         }
        
    }

    public function executeGetApprovalByModuleView(sfWebRequest $request) {
        $viewName=$request->getParameter('viewName');
        $apprivingEmpName=$request->getParameter('appEmpName');
  
        $wfDao = new workflowDao();
        $wfService=new WorkFlowService();
        if (strlen($viewName)) {
           $resultArr = $wfDao->getApprovalListbyModuleView($viewName,$apprivingEmpName);
            
           $wfTypeCode=$resultArr[0]['WorkFlow Type Code'];
           
           $redirectUrl=$wfService->getRiderctUrl($wfTypeCode);
           $redirectUrl="'".$redirectUrl."'";
              
          echo json_encode(array("resultArr" => $resultArr,"redirectUrl"=>$redirectUrl));
            
          
        } else {
            echo "View is Empty";
        }
        die;
    }
    
    public function executeShowWorkflowHistory(sfWebRequest $request){
                
        try {
            $this->culture = $this->getUser()->getCulture();


            $wfService=new WorkFlowService();
            $encrypt = new EncryptionHandler();
            
            $WfmainID=$encrypt->decrypt($request->getParameter('mainId'));              

            $this->sorter = new ListSorter('wf', 'wf', $this->getUser(), array('wfappgrp_code', ListSorter::ASCENDING));

            $this->sorter->setSort(array($request->getParameter('sort'), $request->getParameter('order')));

            if ($request->getParameter('mode') == 'search') {
                if ($request->getParameter('searchMode') != 'all' && trim($request->getParameter('searchValue')) == '') {
                    $this->setMessage('NOTICE', array('Select the field to search'));
                    $this->redirect('default/error');
                }
            }
            $this->searchMode = ($request->getParameter('searchMode') == '') ? 'all' : $request->getParameter('searchMode');
            $this->searchValue = ($request->getParameter('searchValue') == '') ? '' : $request->getParameter('searchValue');

            $this->sort = ($request->getParameter('sort') == '') ? 'ag.wfappgrp_code' : $request->getParameter('sort');
            $this->order = ($request->getParameter('order') == '') ? 'DESC' : $request->getParameter('order');
            $wfDetails = $wfService->getWorkFlowRecordById($WfmainID);
            
            $this->wfDetails = $wfDetails;
            
            
        } catch (Exception $e) {
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING', $errMsg->display());

            $this->redirect('default/error');
        }
        
        
        
        
    }    
    public function executeShowApprovalDetails(sfWebRequest $request) {
        try {
            $WfmainID = $request->getParameter('mainId');
            $approvingEmpName = $request->getParameter('appEmp');

            $wfDao = new workflowDao();

            $WorkflowDetailView = $wfDao->getWorkflowDetailViewDetails($WfmainID, $approvingEmpName);

            die(print_r($WorkflowDetailView));

            if (strlen($viewName)) {
                $resultArr = $wfDao->getApprovalListbyModuleView($viewName, $apprivingEmpName);
                echo json_encode(array("resultArr" => $resultArr));
            } else {
                echo "View is Empty";
            }
        } catch (Exception $e) {
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('workflow/approvalGroupsSummary');
        }
        die;
    }

    public function executeAssignToGroup(sfWebRequest $request) {

        try {
            $this->userCulture = $this->getUser()->getCulture();
            
            $wfService=new GroupApprovalService();

            $groupList = $wfService->getApprovalGroups();
            
            $this->groupList = $groupList;
            
            if (!strlen($request->getParameter('lock'))) {
                
                $this->lockMode = 0;
            } else {
                
                $this->lockMode = $request->getParameter('lock');
            }

            if (strlen($request->getParameter('mode'))) {
                $this->mode = $request->getParameter('mode');
            } else {

                $this->mode = 'edit';
            }
            //print_r($this->lockMode);die;
            if (strlen($request->getParameter('id'))) {
                
                if (strlen($request->getParameter('mode'))) {
                    $this->mode = $request->getParameter('mode');
                } else {
                    $this->mode = 'save';
                }
                
                $groupID = $request->getParameter('id');
                $this->groupID = $groupID;

                $conHandler = new ConcurrencyHandler();                              

                if (!strlen($request->getParameter('lock'))) {

                    $this->lockMode = 0;
                } else {
                    $this->lockMode = $request->getParameter('lock');
                }
                
                $employeeList = $wfService->getEmployeeListbyId($groupID);
                $Empids = array();
                for ($i = 0; $i < count($employeeList); $i++) {

                    $Empids[] = $employeeList[$i]->wf_main_app_employee;
                }
                if (isset($this->lockMode)) {
                    if ($this->lockMode == 1) {


                        

                        $recordLocked2 = $conHandler->setTableLock('hs_hr_wf_group_app_person', array($groupID), 2);

                        if ($recordLocked2) {
                            $this->lockMode = 1;
                        } else {
                            $this->setMessage('WARNING', array($this->getContext()->getI18N()->__('Can not update. Record locked by another user.', $args, 'messages')), false);
                            $this->lockMode = 0;
                        }
                    } else if ($this->lockMode == 0) {

                        $conHandler = new ConcurrencyHandler();

                        $recordLocked2 = $conHandler->resetTableLock('hs_hr_wf_group_app_person', array($groupID), 2);
                        $this->lockMode = 0;
                    }
                }
                if ($this->lockMode == '1') {
                    $editMode = false;
                    $disabled = '';
                } else {
                    $editMode = true;
                    $disabled = 'disabled="disabled"';
                }
                


                $this->employeeList = $employeeList;

                $this->i = 0;
                $this->childDiv = "";

                foreach ($employeeList as $list) {

                    if ($this->userCulture == "en") {
                        $EName = "getEmp_display_name";
                    } else {
                        $EName = "getEmp_display_name_" . $this->userCulture;
                    }
                    if ($list->Employee->$EName() == null) {
                        $empName = $list->Employee->getEmp_display_name();
                    } else {
                        $empName = $list->Employee->$EName();
                    }

                    if ($this->userCulture == "en") {
                        $unit = "title";
                    } else {
                        $unit = "title_" . $this->userCulture;
                    }
                    if ($list->Employee->subDivision->$unit == null) {
                        $displayUnit = $list->Employee->subDivision->title;
                    } else {
                        $displayUnit = $list->Employee->subDivision->$unit;
                    }
                    if (!strlen($displayUnit)) {
                        $displayUnit = "N/A";
                    } else {
                        $displayUnit = $displayUnit;
                    }
                    $this->i = $this->i + 1;
                    //print_r($list->Users->getId());die;
                    $this->childDiv.="<div id='row_" . $this->i . "' style='padding-top:5px; display:inline-block;'>";
                    $this->childDiv.="<div class='centerCol' id='master' style='width:150px;'>";
                    $this->childDiv.="<div id='child'  padding-left:3px;'>" . $list->Employee->getEmployee_id() . "</div>";
                    $this->childDiv.="</div>";

                    $this->childDiv.="<div class='centerCol' id='master' style='width:220px;'>";
                    $this->childDiv.="<div id='child'  padding-left:3px;'>" . $empName . "</div>";
                    $this->childDiv.="</div>";

                    $this->childDiv.="<div class='centerCol' id='master' style='width:120px;'>";
                    $this->childDiv.="<div id='child' padding-left:3px;'>" . $displayUnit . "</div>";
                    $this->childDiv.="</div>";

                    $this->childDiv.="<div class='centerCol' id='master' style='width:100px;'>";
                    $this->childDiv.="<div id='child'  padding-left:3px;'><input type=checkbox name='deleteEmp[]' value=" . $list->Employee->getEmp_number() . " /><input type='hidden' name='hiddenEmpNumber[]' value=" . $list->Employee->getEmp_number() . " > </div>";
                    $this->childDiv.="</div>";
                    $this->childDiv.="</div>";
                }
                if ($request->isMethod('post')) {

                    try {
                        
                        $conn = Doctrine_Manager::getInstance()->connection();
                        $conn->beginTransaction();
                        
                        $wfService->deleteGroupById($_POST['cmbGroupName']);
                         
                        for ($i = 0; $i < count($_POST['hiddenEmpNumber']); $i++) { 
                           $wfGrpApp = new WFGroupAppPerson();
                             //echo $_POST['cmbGroupName'];die;
                             if(strlen($_POST['cmbGroupName'])) {
                                $wfGrpApp->setWfappgrp_code(trim($_POST['cmbGroupName']));
                            } else {
                                $wfGrpApp->setWfappgrp_code(null);
                            }
                            if(strlen($_POST['hiddenEmpNumber'][$i])) {
                                $wfGrpApp->setWf_main_app_employee(trim($_POST['hiddenEmpNumber'][$i]));
                            } else {
                                $wfGrpApp->setWf_main_app_employee(null);
                            }   
                              $wfGrpApp->save();      
                              
                            
                        }
                        $conn->commit();
                    } catch (Doctrine_Connection_Exception $e) {
                        $conn->rollBack();
                        $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
                        $this->setMessage('WARNING', $errMsg->display());
                        $this->redirect('workflow/AssignToGroup?lock=1');
                    } catch (Exception $e) {
                        //$conn->rollBack();
                        $errMsg = new CommonException($e->getMessage(), $e->getCode());
                        $this->setMessage('WARNING', $errMsg->display());

                        $this->redirect('workflow/AssignToGroup?lock=1');
                    }
                    
                    $this->setMessage('SUCCESS', array($this->getContext()->getI18N()->__("Successfully Updated", $args, 'messages')));
                    $this->redirect('workflow/AssignToGroup?lock=0&id=' . $groupID);
                }
            }
        } catch (sfStopException $sf) {
            
        } catch (Doctrine_Connection_Exception $e) {

            $errMsg = new CommonException($e->getPortableMessage(), $e->getPortableCode());
            $this->setMessage('WARNING', $errMsg->display());
            $this->redirect('workflow/AssignToGroup?lock=1');
        } catch (Exception $e) {
            //$conn->rollBack();
            $errMsg = new CommonException($e->getMessage(), $e->getCode());
            $this->setMessage('WARNING', $errMsg->display());

            $this->redirect('workflow/AssignToGroup?lock=1');
        }                        
    }
    
        public function executeDeleteAssignedCapability(sfWebRequest $request) {
        $this->culture = $this->getUser()->getCulture();
        
        $wfservice=new GroupApprovalService();
        $empId = $request->getParameter('empId');
        $groupId = $request->getParameter('groupID');



        $conHandler = new ConcurrencyHandler();
        for ($i = 0; $i < count($empId); $i++) {

            $deleted = $wfservice->deleteAssignedEmployee($empId[$i],$groupId);

             $conHandler->resetTableLock('hs_hr_wf_group_app_person', array($empId[$i]), 1);
        }
        if ($deleted > 0) {
            $msg = "ok";
        } else {
            $msg = "Error";
        }
        echo json_encode(array($msg));
        die;
        
    }
    
    
    public function executeLoadGrid(sfWebRequest $request) {
        $this->culture = $this->getUser()->getCulture();
        $wfservice=new WorkFlowService();
        $empId = $request->getParameter('empid');

        $this->emplist = $wfservice->getEmployee($empId);
        
    }
    public function executeGetListedEmpids(sfWebRequest $request) {
        $wfservice=new WorkFlowService();
        $Cid = $request->getParameter('currentGroup');
        $empidList = $wfservice->GetListedEmpids($Cid);
        $this->empidList = $empidList;
      
    }
    
    
    public function executeSetSessionForID(sfWebRequest $request){
         $id=$request->getParameter('id');
         $_SESSION['workFlowElementId']=$id;
         echo json_encode($_SESSION['workFlowElementId']);
            
        die;
    }
    public function executeUnSetSessionForID(sfWebRequest $request){
         unset($_SESSION['workFlowElementId']);          
         echo json_encode($_SESSION['workFlowElementId']);
            
        die;
    }
    
    

    public function setMessage($messageType, $message = array(), $persist=true) {
        $this->getUser()->setFlash('messageType', $messageType, $persist);
        $this->getUser()->setFlash('message', $message, $persist);
    }

}
