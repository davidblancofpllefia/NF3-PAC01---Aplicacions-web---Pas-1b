<?php
require("class.Logger.php");
class User {

        private $ID;
        private $objPDO;
        private $strTableName; 
        private $arRelationMap;
        private $blForDeletion;

        private $FirstName;
        private $LastName;
        private $Username;
        private $Password;
        private $EmailAddress;

        private $DateLastLogin;
        private $TimeLastLogin;
        private $DateAccountCreated;
        private $TimeAccountCreated;

        public function __construct(PDO $objPDO, $id = NULL) {
                $this->strTableName = "system_user";
                $this->arRelationMap = array(
                    "id" => "ID",
                    "first_name" => "FirstName",
                    "last_name" => "LastName",
                    "username" => "Username",
                    "md5_pw" => "Password",
                    "email_address" => "EmailAddress",
                    "date_last_login" => "DateLastLogin",
                    "time_last_login" => "TimeLastLogin",
                    "date_account_created" => "DateAccountCreated",
                    "time_account_created" => "TimeAccountCreated"
                );
                $this->objPDO = $objPDO;
                if (isset($id)) {
                    $this->ID = $id;
                    $strQuery = "SELECT ";
                    foreach ($this->arRelationMap as $key => $value) {
                        $strQuery .= "\"" . $key . "\",";
                    }
                    $strQuery = substr($strQuery, 0, strlen($strQuery)-1);
                    $strQuery .= " FROM \"" . $this->strTableName . "\" WHERE \"id\" = :eid";
                    $objStatement = $this->objPDO->prepare($strQuery);
                    $objStatement->bindParam(':eid', $this->ID, PDO::PARAM_INT);
                    $objStatement->execute();
                    $arRow = $objStatement->fetch(PDO::FETCH_ASSOC);
                    foreach($arRow as $key => $value) {
                        $strMember = $this->arRelationMap[$key];
                        if (property_exists($this, $strMember)) {
                            if (is_numeric($value)) {
                                eval('$this->' . $strMember . ' = ' . $value . ';');
                            } else {
                                eval('$this->' . $strMember . ' = "' . $value . '";');
                            };
                        };
                    };
                };
            

                $log = Logger::getInstance();
                $log->logMessage('Se ha creado una nueva instancia de la clase User.', Logger::DEBUG);
            }
            

            public function Save() {
                if (isset($this->ID)) {
                    $strQuery = 'UPDATE "' . $this->strTableName . '" SET ';
                    foreach ($this->arRelationMap as $key => $value) {
                        eval('$actualVal = &$this->' . $value . ';');
                        if (isset($actualVal)) {
                            $strQuery .= '"' . $key . "\" = :$value, ";
                        };
                    }
                    $strQuery = substr($strQuery, 0, strlen($strQuery)-2);
                    $strQuery .= ' WHERE "id" = :eid';
                    unset($objStatement);
                    $objStatement = $this->objPDO->prepare($strQuery);
                    $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);
                    foreach ($this->arRelationMap as $key => $value) {
                        eval('$actualVal = &$this->' . $value . ';');
                        if (isset($actualVal)) {
                            if ((is_int($actualVal)) || ($actualVal == NULL)) {
                                $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_INT);
                            } else {
                                $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_STR);
                            };
                        };
                    };
                    $objStatement->execute();
                } else {
                    $strValueList = "";
                    $strQuery = 'INSERT INTO "' . $this->strTableName . '"(';
                    foreach ($this->arRelationMap as $key => $value) {
                        eval('$actualVal = &$this->' . $value . ';');
                        if (isset($actualVal)) {
                             $strQuery .= '"' . $key . '", ';
                             $strValueList .= ":$value, ";
                        };
                    }
                    $strQuery = substr($strQuery, 0, strlen($strQuery) - 2);
                    $strValueList = substr($strValueList, 0, strlen($strValueList) - 2);
                    $strQuery .= ") VALUES (";
                    $strQuery .= $strValueList;
                    $strQuery .= ")";
                    unset($objStatement);
                    $objStatement = $this->objPDO->prepare($strQuery);
                    foreach ($this->arRelationMap as $key => $value) {
                        eval('$actualVal = &$this->' . $value . ';');
                        if (isset($actualVal)) {
                            if ((is_int($actualVal)) || ($actualVal == NULL)) {
                                $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_INT);
                            } else {
                                $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_STR);
                            };
                        };
                    }
                    $objStatement->execute();
                    $this->ID = $this->objPDO->lastInsertId($this->strTableName . "_id_seq");
                }
            

                $log = Logger::getInstance();
                $log->logMessage('Se ha guardado o actualizado un registro en la base de datos.', Logger::INFO);
            }
            


        public function __call($strFunction, $arArguments) {
                $strMethodType = substr($strFunction, 0, 3);
                $strMethodMember = substr($strFunction, 3);
                switch ($strMethodType) {
                    case "set":
                        $result = $this->SetAccessor($strMethodMember, $arArguments[0]);
                        break;
                    case "get":
                        $result = $this->GetAccessor($strMethodMember);
                        break;
                    default:
                        $result = false;
                        break;
                };
            
                $log = Logger::getInstance();
                $log->logMessage('Se ha llamado a la función __call().', Logger::DEBUG);
            
                return $result;
            }
            

        private function SetAccessor($strMember, $strNewValue) {
                if (property_exists($this, $strMember)) {
                    if (is_numeric($strNewValue)) {
                        eval('$this->' . $strMember . ' = ' . $strNewValue . ';');
                    } else {
                        eval('$this->' . $strMember . ' = "' . $strNewValue . '";');
                    };
                } else {
                    return false;
                }
            
                $log = Logger::getInstance();
                $log->logMessage('Se ha llamado a la función SetAccessor().', Logger::DEBUG);
            
                return true;
            }
            
        private function GetAccessor($strMember) {
                if (property_exists($this, $strMember)) {
                    eval('$strRetVal = $this->' . $strMember . ';');
                    // Registro del mensaje utilizando la clase Logger
                    $log = Logger::getInstance();
                    $log->logMessage('Se ha llamado a la función GetAccessor().', Logger::DEBUG);
                    return $strRetVal;
                } else {
                    return false;
                }
            }
            

}

?>