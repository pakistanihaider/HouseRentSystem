<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Created by PhpStorm.
 * User: Syed Haider Hassan
 * Date: 2/4/14
 * Time: 4:09 PM
 */

/**
 * @param PropertiesModel $PropertiesModel Loading Properties Model from Models.
 */
class Properties extends Admin_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('hrs/propertiesmodel');
    }

    function ManageProperties()
    {
        $UserID = $this->data['UserID'];
        if (is_admin($UserID) == TRUE || is_allowed($UserID) == TRUE) {
            $this->data['title'] = "Manage Properties";
            $this->parser->parse('admin/hrs/properties/Manage', $this->data);
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function RentingProperties()
    {
        $UserID = $this->data['UserID'];
        if (is_admin($UserID) == TRUE || is_allowed($UserID) == TRUE) {
            $this->data['title'] = "Renting Properties";
            $this->parser->parse('admin/hrs/properties/Renting', $this->data);
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function propertyDetails($propertyID)
    {
        $UserID = $this->data['UserID'];
        if (is_admin($UserID) == TRUE || is_allowed($UserID) == TRUE) {
            if(!isset($propertyID) || !($propertyID>0)){
                redirect($this->data['errorPage_500']);
                return;
            }

            // Need to Query in Database to get the Property Details to show inside the view.
            $PTable = 'hrs_residentials R';
            $PropertyData = ('ResNo, RT.TypeName, ResRooms, ResKitchens, ResBathrooms, DateRegistered');
            $joins = array(
                array(
                    'table' => 'hrs_residential_type RT',
                    'condition' => 'R.ResTypeID = RT.ResTypeID',
                    'jointype' => 'INNER'
                ),
                array(
                    'table' => 'hrs_vacancy_type VT',
                    'condition' => 'R.VacID = VT.VacID',
                    'jointype' => 'INNER'
                )
            );
            $where = array(
                'R.ResID' =>$propertyID
            );
            $result = $this->Common_Model->select_fields_joined($PropertyData,$PTable,$joins,$where,'');
            $result[0]->ResNo = 'PN-'.$result[0]->ResNo;
            //Showing Date in User Friendly Format on Page.
            $dateRegistered = $result[0]->DateRegistered;
            $result[0]->DateRegistered = date("d M Y", strtotime($dateRegistered));

            /////=====Get Info for Page Top Bar=========\\\\\\.
            //Tenants Info.
            $tbl = 'hrs_tenant_residential';
            $data = ('IsActive');
            $where = array(
                'ResID' => $propertyID
            );

            $tenantsResidential = $this->Common_Model->select_fields_where($tbl,$data,$where);
            if(!empty($tenantsResidential)){
                $tenantsResidentialAssociatedArray = json_decode(json_encode($tenantsResidential),true);
                $countTenants = array_count_values(array_column($tenantsResidentialAssociatedArray, 'IsActive'));
                $result[0]->ActiveTenant = (isset($countTenants[1])?$countTenants[1]:0);
                $result[0]->InActiveTenants = (isset($countTenants[0])?$countTenants[0]:0);
            }
            else{
                $result[0]->ActiveTenant = '0';
                $result[0]->InActiveTenants = '0';
            }
            $tbl= 'hrs_residential_utilities';
            $data = ('COUNT(UtilityID) Utilities');
            $where =array(
                'ResID' => $propertyID
            );
            $utilitiesResidential = $this->Common_Model->select_fields_where($tbl,$data,$where);
            $result[0]->Utilities = $utilitiesResidential[0]->Utilities;
            //Get Active and InActive Tenants from the Result.

            $this->data['PropertyData'] = $result;
            $this->data['propertyID'] = $propertyID;
            $this->data['title'] = "Property Details";
            $this->parser->parse('admin/hrs/properties/PropertyDetails', $this->data);
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function PropertyUtilities()
    {
        $UserID = $this->data['UserID'];
        if (is_admin($UserID) == TRUE || is_allowed($UserID) == TRUE) {
            $this->data['title'] = "Property Utilities";
            $this->parser->parse('admin/hrs/properties/PropertyUtilities', $this->data);
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function AssignTenantProperty($residentialID)
    {
        $UserID = $this->data['UserID'];
        if (is_admin($UserID) == TRUE || is_allowed($UserID) == TRUE) {
            if (!isset($residentialID) && !($residentialID > 0)) {
                redirect($this->data['errorPage_500']);
            }
            $this->data['title'] = "Assign Property To Tenant";
            $this->data['propertyID'] = $residentialID;
            $this->parser->parse('admin/hrs/properties/AssignTenant', $this->data);
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function listResidentials_DT()
    {
        $data = ('ResID,hrs_residentials.ResTypeID,ResNo,TypeName,ResRooms,ResKitchens,ResBathrooms,ResDescription');
        $pTable = "hrs_residentials";
        $joins = array(
            array(
                'table' => 'hrs_residential_type',
                'condition' => 'hrs_residential_type.ResTypeID=hrs_residentials.ResTypeID',
                'type' => 'INNER'
            )
        );
        $id = "ResID";
        $addColumn = "<a href='#editBtnModal' data-toggle='modal' class='editBtnFunc'><i style='color: #666666' class='fa fa-pencil fa-fw fa-2x'></i></a><a href='#' id='deleteBtn' class='deleteBtnFunc'><i style='color: #ff0000' class='fa fa-times fa-fw fa-2x'></i></a>";
        $result = $this->Common_Model->select_fields_joined_DT($data, $pTable, $joins, $where = '', $addColumn, $unsetColumn = '');
        echo $result;
    }

    function loadAllVacancyTypes()
    {/*This Function should load All the Group Names of for Users*/
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $tbl = "hrs_vacancy_type";
                $data = array('VacID', 'TypeName');
                $value = mysql_real_escape_string($this->input->post('term'));
                if (isset($value)) {
                    $field = 'TypeName';
                    $result = $this->Common_Model->select_fields_where_like($tbl, $data, '', FALSE, $field, $value);
                } else {
                    $result = $this->Common_Model->select_fields($tbl, $data);
                }
                if ($result !== FALSE) {
                    //We got the result of all the vacancy types, but we need 1 more type that we need to have in dropdown, "Show All"
                    //First the result is coming in Object Array, We First need to change it to normal Array.
                    $array = json_decode(json_encode($result), true);
                    //Now As Array Has been changed to Normal Array, we can merge our static array data to the original array.
                    $staticDataArray = array(
                        'VacID' => '0',
                        'TypeName' => 'Show All'
                    );
                    //using array_push to merge arrays.
                    array_push($array, $staticDataArray);
                    //finally printing the combined array in json.
                } else {
                    $array[] = array(
                        'VacID' => '0',
                        'TypeName' => 'Show All'
                    );
                }
                print_r(json_encode($array));
            } else {
                redirect($this->data['errorPage_500']);
            }
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function loadAllTenants()
    {
        if ($this->input->is_ajax_request()) {
            $tbl = 'hrs_tenants';
            $data = ('TenantID,users_users.UserID,FullName,Username,CNIC,Avatar');
            $joins = array(
                array(
                    'table' => 'users_users',
                    'condition' => 'hrs_tenants.UserID=users_users.UserID',
                    'jointype' => 'INNER'
                )
            );
            if ($this->input->post()) {
                $value = mysql_real_escape_string($this->input->post('term'));
                if (isset($value)) {
                    $field = 'FullName';
                    $result = $this->Common_Model->select_fields_where_like_join($tbl, $data, $where = '', FALSE, $field, $value, $joins);
                } else {
                    $result = $this->Common_Model->select_fields_joined($data, $tbl, $joins, $where = '');
                }
            }
            print_r(json_encode($result));
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function loadAllPropertyDealers()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $tbl = 'hrs_propertydealers';
                $data = ('PDID,FullName,CNIC,MobileNo,CompanyName');
                $value = $this->input->post('term');
                if (isset($value)) {
                    $field = 'FullName';
                    $result = $this->Common_Model->select_fields_where_like($tbl, $data, '', FALSE, $field, $value);
                } else {
                    $result = $this->Common_Model->select_fields($tbl, $data, FALSE);
                }
                if ($result === FALSE) {
                    $msg = 'FAIL::NO RECORD FOUND::warning';
                    print_r(json_encode($msg));
                } else {
                    print_r(json_encode($result));
                }
            } else {
                return ($this->data['errorPage_500']);
            }
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function loadAllPropertyTypes()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $tbl = 'hrs_residential_type';
                $data = ('ResTypeID,TypeName');
                $value = $this->input->post('term');
                if (isset($value)) {
                    //if term has some value and is not empty then this portion should execute.
                    $field = 'TypeName';
                    $result = $this->Common_Model->select_fields_where_like($tbl, $data, '', FALSE, $field, $value);
                } else {
                    $result = $this->Common_Model->select_fields($tbl, $data, FALSE);
                }
                if ($result === FALSE) {
                    $msg = 'FAIL::NO RECORD FOUND::warning';
                    print_r(json_encode($msg));
                } else {
                    print_r(json_encode($result));
                }

            } else {
                return ($this->data['errorPage_500']);
            }
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function listProperties_DT($vacID = NULL)
    {
        if ($this->input->is_ajax_request()) {

            $data = ('ResID,R.ResTypeID,R.VacID,ResNo,RT.TypeName,ResDescription');
            $pTable = "hrs_residentials R";
            $joins = array(
                array(
                    'table' => 'hrs_residential_type RT',
                    'condition' => 'RT.ResTypeID=R.ResTypeID',
                    'type' => 'INNER'
                ),
                array(
                    'table' => 'hrs_vacancy_type VT',
                    'condition' => 'VT.VacID=R.VacID',
                    'type' => 'INNER'
                )
            );
            if (isset($vacID) && $vacID > 0 && $vacID !== NULL) {
                $where = array(
                    'VT.VacID' => $vacID
                );
                $addColumn = '';
                $result = $this->Common_Model->select_fields_joined_DT($data, $pTable, $joins, $where, $addColumn, $unsetColumn = '');
            } else {
                $result = $this->Common_Model->select_fields_joined_DT($data, $pTable, $joins, $where = '', $addColumn = '', $unsetColumn = '');
            }
            $result = json_decode($result, true);
            foreach ($result['aaData'] as $key => $row) {
                if ($row[2] === '1') {
                    $column = "<a href='#' class='propertyDetailsFunc'><i style='color: #666666' class='fa fa-home fa-fw fa-2x'></i></a><a href='#' id='deleteBtn' class='removeTenantFromPropertyFunc'><i style='color: #ff0000' class='fa fa-minus fa-fw fa-2x'></i></a>";
                    array_push($result['aaData'][$key], $column);
                } elseif ($row[2] === '2') {
                    $column = "<a href='#' class='propertyDetailsFunc'><i style='color: #666666' class='fa fa-home fa-fw fa-2x'></i></a><a href='#' class='assignTenantToPropertyFunc'><i style='color: #3e8f3e' class='fa fa-plus fa-fw fa-2x'></i></a>";
                    array_push($result['aaData'][$key], $column);
                }
            }
            $result = json_encode($result);
            print_r($result);
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function addNewProperty()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $propertyType = mysql_real_escape_string($this->input->post('propertyType'));
                $propertyNo = mysql_real_escape_string($this->input->post('propertyNo'));
                $totalWashrooms = mysql_real_escape_string($this->input->post('totalBathrooms'));
                $totalRooms = mysql_real_escape_string($this->input->post('totalRooms'));
                $totalKitchens = mysql_real_escape_string($this->input->post('totalKitchens'));
                $propertyDescription = mysql_real_escape_string($this->input->post('propertyDescription'));
                $currentDate = $this->data['dbCurrentDate'];
                $loggedInUser = $this->data['UserID'];
                if (empty($propertyNo)) {
                    $tbl = 'hrs_residentials';
                    $data = ('ResID, max(ResNo) as ResNo');
                    $getPropertyNo = $this->Common_Model->select_fields($tbl, $data, TRUE);
                    $propertyNo = $getPropertyNo->ResNo;
                    $propertyNo++;
                    $propertyNo = str_pad($propertyNo, 3, 0, STR_PAD_LEFT);
                }

                //We need to do little Validations for Input to see if data is right for the Database.
                if (!is_numeric($propertyNo)) {
                    echo "FAIL::Property Number Must Be Numeric Value::error";
                    return;
                }
                if (isset($totalRooms) && !is_numeric($totalRooms)) {
                    echo "FAIL::Rooms Should Be a Numeric Value::error";
                    return;
                }
                if (isset($totalKitchens) && !is_numeric($totalKitchens)) {
                    echo "FAIL::Kitchens Must Be a Numeric Value::error";
                    return;
                }
                if (isset($totalWashrooms) && !is_numeric($totalWashrooms)) {
                    echo "FAIL::Washrooms Must Be a Numeric Value::error";
                    return;
                }

                if (isset($propertyType) && isset($propertyNo) && isset($totalRooms)) {
                    $tbl = 'hrs_residentials';
                    $data = array(
                        'TenantID' => 0,
                        'ResTypeID' => $propertyType,
                        'VacID' => 2,
                        'ResNo' => $propertyNo,
                        'ResRooms' => $totalRooms,
                        'ResKitchens' => $totalKitchens,
                        'ResBathrooms' => $totalWashrooms,
                        'ResDescription' => $propertyDescription,
                        'DateRegistered' => $currentDate,
                        'RegisteredBy' => $loggedInUser
                    );
                    $result = $this->Common_Model->insert($tbl, $data);
                }
                if ($result > 0) {
                    echo 'OK::Record Successfully Added::success';
                } else {
                    echo 'FAIL::Some Error Occurred, Record Could Not Be Added::error';
                }
            }
        } else {
            redirect($this->data['errorPage_403']);
        }
    } //End of Add New Property Function

    function editProperty()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $propertyID = $this->input->post('pID');
                if (!empty($propertyID) && is_numeric($propertyID)) {
                    $tbl = 'hrs_residentials';
                    $data = ('ResNo,ResRooms,ResKitchens,ResBathrooms,ResDescription,ResTypeID');
                    $where = array(
                        'ResID' => $propertyID
                    );
                    $result = $this->Common_Model->select_fields_where($tbl, $data, $where);
                    print_r(json_encode($result));
                    return;
                }
            }
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function updateProperty()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $propertyType = mysql_real_escape_string($this->input->post('propertyType'));
                $propertyNo = mysql_real_escape_string($this->input->post('propertyNo'));
                $totalWashrooms = mysql_real_escape_string($this->input->post('totalBathrooms'));
                $totalRooms = mysql_real_escape_string($this->input->post('totalRooms'));
                $totalKitchens = mysql_real_escape_string($this->input->post('totalKitchens'));
                $propertyDescription = mysql_real_escape_string($this->input->post('propertyDescription'));
                $propertyID = mysql_real_escape_string($this->input->post('PID'));
                $currentDate = $this->data['dbCurrentDate'];
                $loggedInUser = $this->data['UserID'];

                //We need to do little Validations for Input to see if data is right for the Database.
                if (empty($propertyNo) || !is_numeric($propertyNo)) {
                    echo "FAIL::Property Number Must Be Numeric Value::error";
                    return;
                }
                if (isset($totalRooms) && !is_numeric($totalRooms)) {
                    echo "FAIL::Rooms Should Be a Numeric Value::error";
                    return;
                }
                if (isset($totalKitchens) && !is_numeric($totalKitchens)) {
                    echo "FAIL::Kitchens Must Be a Numeric Value::error";
                    return;
                }
                if (isset($totalWashrooms) && !is_numeric($totalWashrooms)) {
                    echo "FAIL::Washrooms Must Be a Numeric Value::error";
                    return;
                }
                //Now Here the Main Working Functions Starts if Everything on Top Worked Fine.
                if (isset($propertyNo) && isset($totalRooms) && isset($propertyType)) {
                    $tbl = 'hrs_residentials';
                    $data = array(
                        'ResNo' => $propertyNo,
                        'ResRooms' => $totalRooms,
                        'ResKitchens' => $totalKitchens,
                        'ResBathrooms' => $totalWashrooms,
                        'ResDescription' => $propertyDescription,
                        'DateUpdated' => $currentDate,
                        'UpdatedBy' => $loggedInUser,
                        'ResTypeID' => $propertyType
                    );
                    $fields = array(
                        'ResID' => $propertyID
                    );
                    $result = $this->Common_Model->update($tbl, $fields, $data);
                    if ($result === true) {
                        echo 'OK::Record Successfully Updated::success';
                        return;
                    } else {
                        echo 'FAIL::Some Database Error, Record Could Not Be Updated::error';
                        return;
                    }
                } else {
                    echo "FAIL::Fill the Form Correctly::error";
                    return;
                }

            }
        }
    }

    function deleteProperty()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $propertyID = $this->input->post('pID');
                $tbl = 'hrs_residentials';
                $condition = array(
                    'ResID' => $propertyID
                );
                $result = $this->Common_Model->delete($tbl, $condition);
                if ($result === TRUE) {
                    echo "OK::Record Successfully Deleted::success";
                } elseif ($result === FALSE) {
                    echo "FAIL:: Some Database Error, Record Could Not Be Deleted::error";
                }
            }
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function addNewPropertyDealer()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $companyName = mysql_real_escape_string($this->input->post('companyName'));
                $agentName = mysql_real_escape_string($this->input->post('agentName'));
                $cnic = mysql_real_escape_string($this->input->post('cnic'));
                $mobileNo = mysql_real_escape_string($this->input->post('mobileNo'));
                $email = mysql_real_escape_string($this->input->post('email'));

                if (empty($companyName) || empty($agentName) || empty($mobileNo)) {
                    return "FAIL::SomeData is Still Missing, Please Ensure you Filled the Forms Correctly::error";
                }
                $tbl = 'hrs_propertydealers';
                $data = array(
                    'CompanyName' => $companyName,
                    'FullName' => $agentName,
                    'CNIC' => $cnic,
                    'MobileNo' => $mobileNo,
                    'Email' => $email
                );
                $result = $this->Common_Model->insert_record($tbl, $data);
                if ($result > 0) {
                    echo "OK::Record Successfully Created::success::" . $result;
                } else {
                    echo "FAIL:: Some Error, Error Occurred In Database::error";
                }
            }
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function assignTenantToProperty()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $tenantID = mysql_real_escape_string($this->input->post('tenantID'));
                $securityDeposit = mysql_real_escape_string($this->input->post('securityDeposit'));
                $downPayment = mysql_real_escape_string($this->input->post('downPayment'));
                $startingRent = mysql_real_escape_string($this->input->post('startingRent'));
                $state = mysql_real_escape_string($this->input->post('state'));
                $referencerName = mysql_real_escape_string($this->input->post('referenceName'));
                $thirdParty = mysql_real_escape_string($this->input->post('thirdParty'));
                $resID = mysql_real_escape_string($this->input->post('resID'));

                if (!isset($resID) && !($resID > 0)) {
                    redirect($this->data['errorPage_500']);
                    return;
                }

                if (isset($tenantID) && $tenantID > 0 && isset($startingRent) && $startingRent > 0 && $securityDeposit > 0 && isset($state)) {

                    if ($state === 'thirdParty' && !(isset($thirdParty) && $thirdParty > 0)) {
                        echo 'FAIL:: If you choose Third Party, You Must Provide Third Party Details::error';
                        return;
                    }
                    $PTable = 'hrs_tenant_residential';
                    $formData = array(
                        'TenantID' => $tenantID,
                        'ResID' => $resID,
                        'StartingRent' => $startingRent,
                        'SecurityDeposit' => $securityDeposit,
                        'DownPayment' => $downPayment,
                        'AssignedBy' => $this->data['UserID'],
                        'DateAssigned' => $this->data['dbCurrentDate']
                    );

                    $allowedExt = array('jpeg', 'jpg', 'png', 'gif', 'pdf');
                    if (isset($_FILES['image']['name'])) {
                        $FileName = $_FILES['image']['name'];
                        $ext = end(explode('.', $FileName));
                        if (!in_array(strtolower(end(explode('.', $FileName))), $allowedExt)) {
                            echo "FAIL:: Only Images JPEG, PNG and GIF and File PDF are Allowed, No Other Extensions Are Excepted::error";
                            return;
                        } else {
                            //we need userId for that Tenant, so Lets Find UserID for this selected Tenant.
                            $tbl = 'hrs_tenants';
                            $data = ('UserID');
                            $where = array(
                                'TenantID' => $tenantID
                            );
                            $result = $this->Common_Model->select_fields_where($tbl, $data, $where, TRUE);
                            $userID = $result->UserID;
                            if (!isset($userID) && !($userID > 0)) {
                                echo 'FAIL::No User is Assigned to This Selected Tenant::error';
                                return;
                            }
                            //End of Finding UserID for the Selected Tenant.
//                            Now We Need to Set the Users Upload Directory and Upload Path to Upload the Posted File
                            $uploadPath = FCPATH . 'uploads\\users\\' . $userID . '\\files\\' . strtolower($ext) . '\\';
                            $uploadDirectory = FCPATH . 'uploads\\users\\' . $userID . '\\files\\' . strtolower($ext);
                            $FileName = "HRS_User_" . $userID . "_" . time() . "." . $ext;
                            if (!is_dir($uploadDirectory)) {
                                if (!mkdir($uploadDirectory, 0755, true)) {
                                    echo "FAIL::Directory Could Not Be Created On the Server, Also No Record Created.::error";
                                    return;
                                }
//                                mkdir($uploadDirectory, 0755);
                            }
                            move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath . $FileName);
                            $formData['AgreementCopy'] = $FileName;
                            $result = $this->PropertiesModel->assignTenantToProperty($PTable, $formData);
                            if ($result === TRUE) {
                                echo "OK::Tenant Successfully Assigned To Property::success";
                                return;
                            } elseif ($result === FALSE) {
                                echo "FAIL::Some Database Error Occurred, No Record Created::error";
                                return;
                            } else {
                                echo $result;
                                return;
                            }
                        }
                    }
                } else {
                    echo "FAIL::You Must Fill the Form Correctly::error";
                    return;
                }
            }
        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function removeTenantFromProperty()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $resID = mysql_real_escape_string($this->input->post('resID'));
                $result = $this->PropertiesModel->removeTenantFromProperty($resID);
                if ($result === TRUE) {
                    echo "OK::Success, Record Successfully Deleted::success";
                    return;
                } elseif ($result === FALSE) {
                    echo "FAIL::Some Database Error, Record Could Not Be Deleted::error";
                }
            }
        }
    }

    /**
     * @loadAllUtilities This Function should Load All the Utilities.
     */
    function loadAllUtilityTypes()
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $tbl = 'hrs_utility_type';
                $data = ('UTID,UName');
                $value = $this->input->post('term');
                if (isset($value)) {
                    //if term has some value and is not empty then this portion should execute.
                    $field = 'UName';
                    $result = $this->Common_Model->select_fields_where_like($tbl, $data, '', FALSE, $field, $value);
                } else {
                    $result = $this->Common_Model->select_fields($tbl, $data, FALSE);
                }

                if ($result !== FALSE) {
                    //We got the result of all the Utility types, but we need 1 more type that we need to have in dropdown, "Show All"
                    //First the result is coming in Object Array, We First need to change it to normal Array.
                    $array = json_decode(json_encode($result), true);
                    //Now As Array Has been changed to Normal Array, we can merge our static array data to the original array.
                    $staticDataArray = array(
                        'UTID' => '0',
                        'UName' => 'Show All'
                    );
                    //using array_push to merge arrays.
                    array_push($array, $staticDataArray);
                    //finally printing the combined array in json.
                } else {
                    $array[] = array(
                        'UTID' => '0',
                        'UName' => 'Show All'
                    );
                }
                print_r(json_encode($array));
            } else {
                redirect($this->data['errorPage_500']);
            }
        } else {
            redirect($this->data['errorPage_403']);
        }

    }

    function listPropertiesWithUtilities_DT($utilityTypeID = '')
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $data = ('R.ResID,R.ResNo,RT.TypeName,R.ResDescription,U.Number,UT.UName');
                $pTable = "hrs_residentials R";
                $joins = array(
                    array(
                        'table' => 'hrs_residential_type RT',
                        'condition' => 'RT.ResTypeID=R.ResTypeID',
                        'type' => 'INNER'
                    ),
                    array(
                        'table' => 'hrs_residential_utilities RU',
                        'condition' => 'RU.ResID=R.ResID',
                        'type' => 'LEFT'
                    ),
                    array(
                        'table' => 'hrs_utilities U',
                        'condition' => 'U.UtilityID=RU.UtilityID',
                        'type' => 'LEFT'
                    ),
                    array(
                        'table' => 'hrs_utility_type UT',
                        'condition' => 'UT.UTID=U.UtilityTypeID',
                        'type' => 'LEFT'
                    )
                );
                if (isset($utilityTypeID) && $utilityTypeID > 0 && $utilityTypeID !== NULL) {
                    $where = array(
                        'UT.UTID' => $utilityTypeID
                    );
                    $addColumn = '';
                    $result = $this->Common_Model->select_fields_joined_DT($data, $pTable, $joins, $where, $addColumn, $unsetColumn = '');
                } else {
                    $result = $this->Common_Model->select_fields_joined_DT($data, $pTable, $joins, $where = '', $addColumn = '', $unsetColumn = '');
                }
                $result = json_decode($result, true);
                foreach ($result['aaData'] as $key => $row) {
                    if ($row[4] === NULL || empty($row[4]) || empty($row[5]) || $row[5] === NULL) {
                        $column = "<a href='#' class='assignUtilityToProperty'><i style='color: #ff0000' class='fa fa-plus fa-fw fa-2x'></i></a>";
                        array_push($result['aaData'][$key], $column);
                    } elseif ($row[4] !== null && $row[5] !== null) {
                        $column = "<a href='#' class='showUtilityDetailsForProperty'><i style='color: #3e8f3e' class='fa fa-list-alt fa-fw fa-2x'></i></a><a href='#' class='assignUtilityToProperty'><i style='color: #3e8f3e' class='fa fa-plus fa-fw fa-2x'></i></a>";
                        array_push($result['aaData'][$key], $column);
                    }
                }
                $result = json_encode($result);
                print_r($result);
            } else {
                redirect($this->data['errorPage_500']);
            }

        } else {
            redirect($this->data['errorPage_403']);
        }
    }

    function listPropertyTenantsHistory_DT($propertyID)
    {
        if ($this->input->is_ajax_request()) {
            if ($this->input->post()) {
                $PTable = 'hrs_residentials R';
                $data = ('T.TenantID,TR.IsActive,FullName,FatherName,CNIC,Mobile,DateAssigned,DateRevoked');
                $joins = array(
                    array(
                        'table' => 'hrs_tenant_residential TR',
                        'condition' => 'R.ResID = TR.ResID',
                        'type' => 'INNER'
                    ),
                    array(
                        'table' => 'hrs_tenants T',
                        'condition' => 'TR.TenantID = T.TenantID',
                        'type' => 'INNER'
                    ),
                    array(
                        'table' => 'users_users U',
                        'condition' => 'T.UserID = U.UserID',
                        'type' => 'INNER'
                    )
                );
                $where = array(
                    'R.ResID' => $propertyID
                );
                $orderBy = "desc";
                $orderByColumn = "TR.IsActive";
                $result = $this->Common_Model->select_fields_joined_DT($data, $PTable, $joins, $where, '', '',$orderBy,$orderByColumn);
                $resultAssociatedArray = json_decode($result,true);
                //Replace 0 with InActive and Replace 1 with Active.
                foreach($resultAssociatedArray['aaData'] as $key=>$value){
                    if($value[1] === '0'){
                        $array = array(
                            1 => '<span class="label label-default">InActive</span>'
                        );
                    } elseif($value[1] === '1'){
                        $array = array(
                            1 => '<span class="label label-success">Active</span>'
                        );
                    }
                    $resultAssociatedArray['aaData'][$key] = array_replace($resultAssociatedArray['aaData'][$key],$array);
                }
                print_r(json_encode($resultAssociatedArray));
                return;
            } else {
                redirect($this->data['errorPage_503']);
            }
        } else {
            redirect($this->data['errorPage_403']);
        }
    }
    function listPropertyUtilities_DT($propertyID){
        if($this->input->is_ajax_request()){
            if($this->input->post()){
                $PTable = 'hrs_utilities U';
                $data = ('U.UtilityID,UName,Number,ConsumerNo,UtilityRegisteredUnderNameOf');
                $joins = array(
                    array(
                        'table' => 'hrs_utility_type UT',
                        'condition' => 'U.UtilityTypeID = UT.UTID',
                        'type' => 'INNER'
                    ),
                    array(
                        'table' => 'hrs_residential_utilities RU',
                        'condition' => 'U.UtilityID = RU.UtilityID',
                        'type' => 'INNER'
                    )
                );
                $where = array(
                  'RU.ResID' => $propertyID
                );
                $result = $this->Common_Model->select_fields_joined_DT($data,$PTable,$joins,$where,'','');
                print_r($result);
                return;
            }
            else{
                redirect($this->data['errorPage_500']);
            }
        }
        else{
            redirect($this->data['errorPage_403']);
        }
    }
}