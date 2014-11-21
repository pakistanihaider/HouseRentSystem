<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * User: HaiderHassan
 * Date: 8/29/14
 * Time: 5:55 PM
 */
class usersManageUsers extends Admin_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->library('Datatables.php');
        $this->load->library('table');
        $this->load->helper('dataTables');
        $this->load->model('system/configuration');
    }

/*//////////////////////////////////////////////////////////////////////
////////////////////Views Under the Manage Users Main Menu/////////////////////
///////////////////////////////////////////////////////////////////////*/
    function CreateUser()
    {
        $this->data['title'] = "Create New User";
        $this->parser->parse('admin/users/manage_users/CreateUser', $this->data);
    }
    function UpdateUser($userID)
    {
        $this->data['title'] = "Edit User Information";
        $this->data['update_page'] = "admin/users/manage_users/UpdateUser";
        //Now Query below to get data for the selected User.
        $PTable="users_users";
        $where = array(
            'UserID' => $userID
        );
        $data = array('*');
        $joins = array(
            array(
                'table' => 'users_groups',
                'condition' => 'users_users.GroupID = users_groups.GroupID',
                'jointype' => 'INNER'
            )
        );
        $result = $this->Common_Model->select_fields_joined($data,$PTable,$joins,$where);
        $this->data['UserData'] = $result;
        $this->parser->parse('admin/users/manage_users/UpdateUser', $this->data);
    }
    function ListUsers()
    {
        $this->data['title'] = "List Users";
        $this->parser->parse('admin/users/manage_users/ListUsers', $this->data);
    }
    function ListUsersGroups(){
        $this->data['title'] = "Manage Users Groups";
        $this->parser->parse('admin/users/manage_users/ListUsersGroups', $this->data);
    }

    /*//////////////////////////////////////////////////////////////////////
    ////////////////////Functions for the Above Views/////////////////////
    ///////////////////////////////////////////////////////////////////////*/

    /**
     * @function CreateUser_Action This Function Will Check for the Following Checks
     *  1. If the Request is Generated By Ajax.
     *  2. If the Request Has Some Data Posted.
     *  3. If Password and Confirm Password Matches.
     *  4. If Email Already Exist.
     *  5. If CNIC Already Exist.
     *  6. If The Extension of Selected Input File Matches with Allowed Extension's.
     */
    function CreateUser_Action()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $fullName = $this->input->post('fullName');
                $fatherName = $this->input->post('fatherName');
                $username = $this->input->post('username');
                $email = $this->input->post('userEmail');
                $cnic = $this->input->post('cnic');
                $mobile = $this->input->post('mobileNo');
                $pass = $this->input->post('pass');
                $confirmPass = $this->input->post('pass2');
                $theme = $this->input->post('theme');
                $userGroupID = $this->input->post('selectGroup');
                if ($pass === $confirmPass) {
                    $pass = $this->hashPassword($pass);
                    $data_users = array(
                        'UserName' => $username,
                        'Password' => $pass,
                        'FullName' => $fullName,
                        'FatherName' => $fatherName,
                        'Email' => $email,
                        'Mobile' => $mobile,
                        'CNIC' => $cnic,
                        'Theme' => $theme,
                        'GroupID' => $userGroupID
                    );
                    $table = "users_users";
                    $data = ('Email,CNIC');
                    $where= array(
                        'Email' => $email
                    );
                    //Before Insertion, We need to do 1 Final Check, that if email Address and CNIC are Unique or Not.
//                    checking for email
                    $userEmail = $this->Common_Model->select_fields_where($table,$data,$where,TRUE);
                    if(isset($userEmail->Email)){
                        echo "FAIL::Email Already Exist, Please Login if you Are Already Registered.::error";
                        return; //return the function if email already exist.
                    }
//                    checking for CNIC Now.
                    $where= array(
                        'CNIC' => $cnic
                    );
                    $userCNIC = $this->Common_Model->select_fields_where($table,$data,$where,TRUE);
                    if(isset($userCNIC->CNIC)){
                        echo "FAIL::CNIC Already Exist, Please Login if you Are Already Registered.::error";
                        return; //return the function if cnic already exist.
                    }

                    //Now if values are unique we can continue to Add New User.
                    $newUserID = $this->Common_Model->insert_record($table, $data_users);
                    if(isset($newUserID) && $newUserID!='' && $newUserID>0){
                        //If the there are some Input Post Done then we will check if Image was also Posted.
                        //Before checking for image, first we need to set the Allowed Extensions for the Uploaded Image.
                        $allowedExt = array('jpeg','jpg','png','gif');
                        if(isset($_FILES['image']['name']))
                        {
                            $FileName = $_FILES['image']['name'];
                            $ext = end(explode('.',$FileName));

                            if(!in_array(strtolower(end(explode('.',$FileName))),$allowedExt))
                            {
                                echo "FAIL:: Only Image JPEG, PNG and GIF Images Allowed, No Other Extensions Are Excepted::error";
                                return;
                            }else
                            {
                                $uploadPath = './uploads/users/'.$newUserID.'/';
                                $uploadDirectory = './uploads/users/'.$newUserID;
                                $FileName = "HRS_User_".$newUserID."_".time().".".$ext;
                                if(!is_dir($uploadDirectory)){
                                mkdir($uploadDirectory, 0755);
                                }
                                move_uploaded_file($_FILES['image']['tmp_name'],$uploadPath.$FileName);
                                $updateFilData['Avatar'] = $FileName;
                                $where = array(
                                    'UserID' => $newUserID
                                );
                                $result = $this->Common_Model->update($table,$where,$updateFilData);
                                if($result==true){
                                    echo "OK::New User Successfully Created::success";
                                    return;
                                }
                            }
                        }else{
                            if ($newUserID > 0) {
                                echo "OK::New User Successfully Created::success";
                                return;
                            }
                        }
                    }
                }
                /*          $post = array();
                            foreach ( $_POST as $key => $value )
                            {
                                $post[$key] = $this->input->post($key);
                            }
                            var_dump($post);*/
            }
        }
    }
  //Function for Updating User Details.
    function UpdateUser_Action($userID){
        //check if Request is posted Through Ajax
        if($this->input->is_ajax_request()){
        //check if Values are Posted
            if($this->input->post()){
                $username = $this->input->post('username');
                $fullName = $this->input->post('fullName');
                $email = $this->input->post('userEmail');
                $mobile = $this->input->post('mobileNo');
                $cnic = $this->input->post('cnic');
                $currentPass = $this->input->post('currentPass');
                $pass = $this->input->post('pass');
                $confirmPass = $this->input->post('pass2');
                $theme = $this->input->post('theme');
                $userGroup = $this->input->post('selectGroup');
                if(strpos($userGroup,',') == TRUE){
                    $exp = explode(",",$userGroup);
                    $userGroupID = $exp[0];
                }else{
                    $userGroupID = $userGroup;
                }
                $table = 'users_users';
                //Field is the column name of the table on the base of which query should update the row.
                $field='UserID';
                if($currentPass!=''){
                    if($pass===$confirmPass){
                        $data = array(
                            'Username' => $username,
                            'FullName' => $fullName,
                            'Email'  => $email,
                            'Password' => $pass,
                            'Mobile' => $mobile,
                            'CNIC'   => $cnic,
                            'theme'  => $theme,
                            'GroupID' => $userGroupID
                        );
                        $result = $this->Common_Model->update_query($table,$field,$userID,$data);
                        if($result=true){
                            echo "OK::Record Successfully Updated::success";
                        }
                        else{
                            echo "FAIL::Some Database Error, Record Could Not be Updated::success";
                        }
                    }
                    else{
                        echo "password do not match with confirm password";
                    }
                }
                elseif($currentPass==''){
                    $data = array(
                        'Username' => $username,
                        'FullName' => $fullName,
                        'Email'  => $email,
                        'Mobile' => $mobile,
                        'CNIC'   => $cnic,
                        'theme'  => $theme,
                        'GroupID' => $userGroupID
                    );
                    $result = $this->Common_Model->update_query($table,$field,$userID,$data);
                    if($result=true){
                        echo "OK::Record Successfully Updated::success";
                    }
                    else{
                        echo "FAIL::Some Database Error, Record Could Not be Updated::success";
                    }
                }
            }
        }

    }

    function CreateUser_firstStepValidation()
    {
        //Validation For File Upload.
        $allowedExt = array('jpeg','jpg','png','gif');
        if(isset($_FILES['userDefaultAvatar']['name']))
        {
            $FileName = $_FILES['userDefaultAvatar']['name'];
            $ext = end(explode('.',$FileName));
            if(!in_array(strtolower(end(explode('.',$FileName))),$allowedExt))
            {
                echo "FAIL:: Only Image Upload is Allowed, No Other Extensions Are Excepted::error";
                return;
            }
        }
        //Validation for Email, Check if email is not already been taken.
        if ($this->input->post('userEmail')) {
            $userEmail = $this->input->post('userEmail');
            $table = "users_users";
            $data = array('Email');
            $where = array(
                'Email' => $userEmail
            );
            $result = $this->Common_Model->select_fields_where($table, $data, $where);
            if ($result > 0) {
                //error message for email already exist in database.
                echo "FAIL::Email Already Exist::error";
                return;
            } else {
                echo "OK::Hurray! Validation Completed::success";
            }
        } else {
            echo "FAIL::Email field can not be left empty.::error";
        }
    }
    function UpdateUser_firstStepValidation($userID){
        //Validation for Email, Check if email is not already been taken.
        if($this->input->post('userEmail')){
           $userEmail = $this->input->post('userEmail');
            $table = "users_users";
            $data = array('Email');
            //Using UserID to make sure that query dont check for email against the same User.
            $where = array(
                'Email' => $userEmail,
                'UserID !=' => $userID
            );
            $result = $this->Common_Model->select_fields_where($table, $data,$where);
            if($result>0){
                //error message for email already exist in database.
                echo "FAIL::Email Already Exist::error";
            }
            else{
                echo "OK::Hurray! Validation Completed::success";
            }
        }
        else{
            echo "FAIL::Email field can not be left empty.::error";
        }
    }
    function updateUserProfilePicture(){
        if($this->input->is_ajax_request()){
            if($this->input->post()){
                $selectedUserID = mysql_real_escape_string($this->input->post('u-id'));
                //Coding for updating the User Profile Picture Goes Under this If Statement.
                $allowedExt = array('jpeg','jpg','png','gif');
                if(isset($_FILES['image']['name']))
                {
                    $FileName = $_FILES['image']['name'];
                    $ext = end(explode('.',$FileName));

                    if(!in_array(strtolower(end(explode('.',$FileName))),$allowedExt))
                    {
                        echo "FAIL:: Only Image JPEG, PNG and GIF Images Allowed, No Other Extensions Are Excepted::error";
                        return;
                    }else
                    {
                        //First We Need to Delete the Old Image From Directory.
                        $uploadPath = './uploads/users/'.$selectedUserID.'/';
                        $uploadDirectory = './uploads/users/'.$selectedUserID;
                        $FileName = "HRS_User_".$selectedUserID."_".time().".".$ext;
                        if(!is_dir($uploadDirectory)){
                            mkdir($uploadDirectory, 0755);
                        }
                        move_uploaded_file($_FILES['image']['tmp_name'],$uploadPath.$FileName);
                        $updateFilData['Avatar'] = $FileName;
                        $where = array(
                            'UserID' => $selectedUserID
                        );
                        $table = 'users_users';
                        $result = $this->Common_Model->update($table,$where,$updateFilData);
                        if($result==true){
                            echo "OK::Record Successfully Updated::success";
                            return;
                        }
                    }
                }
            }
        }
    }
    function DeleteUser_Action($userID){
        if($userID!==1){
        $where = array(
            'UserID' =>$userID
        );
        $tbl = "users_users";
        $result = $this->Common_Model->delete($tbl,$where);
        if ($result==true){
            echo "OK::Record Successfully Deleted::success";
        }
        else{
            echo "FAIL::Some Error, Record Could not Be Deleted.::error";
        }
        }
        else{
            echo "FAIL::You can Not Delete the Super Admin.::error";
        }
    }

    function loadAllUserGroups(){
        /*This Function should load All the Group Names of for Users*/
        $tbl = "users_groups";
        $data = array('GroupID','GroupName');
        $result = $this->Common_Model->select_fields($tbl,$data);
        print_r(json_encode($result));
    }

    function listUsers_DT()
    {
        //Code to List Data in in DataTables for Listing of Users
        $data = ('UserID,users_users.GroupID,FullName,Username,Mobile,Email,GroupName');
        $pTable = "users_users";
        $joins = array(
            array(
                'table' => 'users_groups',
                'condition' => 'users_groups.GroupID=users_users.GroupID',
                'type' => 'INNER'
            )
        );
        $id = "UserID";
        $addColumn = "<a href='#editBtnModal' data-toggle='modal' class='editBtnFunc'><i style='color: #666666' class='fa fa-pencil fa-fw fa-2x'></i></a><a href='#' id='deleteBtn' class='deleteBtnFunc'><i style='color: #ff0000' class='fa fa-times fa-fw fa-2x'></i></a>";
        $result = $this->Common_Model->select_fields_joined_DT($data, $pTable,$joins, $where = '', $addColumn, $unsetColumn='');
        echo $result;
    }
    function listGroups_DT()
    {
        //Code to List Data in in DataTables for Listing of Users
        $data = ('GroupID,GroupName,GroupDescription');
        $table = "users_groups";
        $addColumn = "<a href='#editBtnModal' data-toggle='modal' class='editBtnFunc'><i style='color: #666666' class='fa fa-pencil fa-fw fa-2x'></i></a><a href='#' id='deleteBtn' class='deleteBtnFunc'><i style='color: #ff0000' class='fa fa-times fa-fw fa-2x'></i></a>";
        $result = $this->Common_Model->select_fields_joined_DT($data, $table,$joins='', $where = '', $addColumn);
        echo $result;
    }
    function addNewGroup(){
        //function to create new group.
        if($this->input->post()){
            $groupName = $this->input->post('GroupName');
            $groupDesc = $this->input->post('GroupDesc');
            if($groupName!=''){
                $data = array(
                  'GroupName' => $groupName,
                  'GroupDescription' => $groupDesc
                );
                $table = 'users_groups';
                $result = $this->Common_Model->insert_record($table,$data);
                if($result>0){
                    echo "OK::Record Successfully Added::success";
                }
                else{
                    echo "FAIL::Some Error, Record Could Not Be Added::error";
                }
            }
        }
    }
    function getGroupData($groupID){
        //function to get the details of the selected group
        if($groupID>0){
            $table = "users_groups";
            $data = ('GroupName,GroupDescription');
            $where = array(
                'GroupID' => $groupID
            );
            $result = $this->Common_Model->select_fields_where($table, $data,$where);
            print json_encode($result);
        }
    }
    function deleteGroup($groupID){
        //Delete the Selected Group
        if($groupID>0 && $groupID!=1){
            $table = 'users_groups';
            $condition = array(
                'GroupID' => $groupID
            );
            $result = $this->Common_Model->delete($table,$condition);
            if($result == TRUE){
                echo "OK::Record Successfully Deleted::success";
            }
        }
        elseif($groupID==1){
            echo "FAIL::SuperAdmin Group Can Not Be Deleted.::error";
        }
        else{
            echo "FAIL:: Some Database Error Occurred, Data Could Not Be Deleted::error";
        }
    }
    function UpdateGroupData(){
        //This Function Should Be Responsible for Editing/Updating a selected Group
        if($this->input->post()){
            $groupID = $this->input->post('GroupID');
            $groupName = $this->input->post('GroupName');
            $groupDesc = $this->input->post('GroupDesc');
            $data = array(
              'GroupName' => $groupName,
              'GroupDescription' => $groupDesc
            );
            $table = 'users_groups';
            $field='GroupID';
            $result = $this->Common_Model->update_query($table,$field,$groupID,$data);
            if($result){
                echo "OK::Record Successfully Updated::success";
            }
            else{
                echo "FAIL::Some Error, Record Could Not Be Updated::error";
            }
        }
        else{
            echo "FAIL::No Data Posted, You Must Enter Data.::warning";
        }
    }
}