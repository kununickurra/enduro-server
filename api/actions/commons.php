<?php

interface ApiAction {
    function execute();
}

abstract class BaseApiAction implements ApiAction
{
    private static $SUCCESSFUL_RETURN_CODES = array(
        "GET"  => 200,  // OK
        "POST" => 201,  // Created
        "PUT"  => 200); // OK

    protected $slim;
    protected $countryId;
    protected $dataValidator;
    protected $dataAccess;

    abstract protected function performApiCall(array $urlParams, array $queryParams, $body);

    function validateCountry($country_code) {
        $sql = "SELECT dim_country_id 
              FROM dim_country where country_code = UPPER('".$country_code."') and end_date = '9999-12-31'
              AND oms_support = 1";
        $country_id = $this->dataAccess->queryForObject($sql);
        if(!$country_id) {
            throw new BadRequestException(new ApiError("401", "Invalid country code provided in URL parameter [".$country_code."] or no OMS support for this country"));
        }
        return $country_id["dim_country_id"];
    }

    public function execute() {

        $this->dataValidator = new DataValidator();
        $this->dataAccess = DataAccessManagerFactory::getInstance();

        $request = Slim::getInstance()->request();
        $response = Slim::getInstance()->response();

        $this->setResponseHeaders($response);
        $this->validateContentType($request);

        $queryParameters = $request->get();

        try {
            $requestBody = $this->validateJson($request->getBody());
            $matchedRoot = Slim::getInstance()->router()->getMatchedRoutes();
            $urlParameters = $matchedRoot[0]->getParams();

            // Validate country code and set the countryId field.
            if(!empty($urlParameters["country_code"])) {
                $urlParameters["country_id"] = $this->validateCountry($urlParameters["country_code"]);
                $this->countryId = $urlParameters["country_id"];
            }

            $responseBody = $this->performApiCall($urlParameters, $queryParameters, $requestBody);
            // If we are here it means that the API call was successful
            $response->status(self::$SUCCESSFUL_RETURN_CODES[$request->getMethod()]);

        } catch (BadRequestException $e) {
            $responseBody = $this->returnJson($e->getApiError()->to_json());
            Slim::getInstance()->halt(400, $responseBody);
        } catch (EntityNotFoundException $e) {
            $apiError = new ApiError("404", "Entity [". $e->getEntityName() ."] not found with provided identification [". $e->getId() ."]");
            Slim::getInstance()->halt(404, $this->returnJson($apiError->to_json()));
        } catch (UnauthorizedAccessException $e) {
            $apiError = new ApiError("401", $e->getMessage());
            Slim::getInstance()->halt(401, $this->returnJson($apiError->to_json()));
        } catch (Exception $e) {
            $apiError = new ApiError("500", "Internal error happened inside OMS, please contact you system administrator...");
            error_log($e);
            Slim::getInstance()->halt(500, $this->returnJson($apiError->to_json()));
        }

        if($responseBody !== NULL) {
            $response->body($this->returnJson($responseBody));
        }
    }

    public function validateField($fieldName, $source, $dataType, $mandatory = false, $defaultValue = NULL) {
        return $this->dataValidator->validateData($fieldName, $source, $dataType, "411", $mandatory, $defaultValue);
    }

    public function validateUrlParameter($fieldName, $source, $dataType) {
        return $this->dataValidator->validateData($fieldName, $source, $dataType, "401", true, NULL);
    }

    public function validateQueryParameter($fieldName, $source, $dataType, $mandatory = false, $defaultValue = NULL) {
        return $this->dataValidator->validateData($fieldName, $source, $dataType, "402", $mandatory, $defaultValue);
    }

    public function isFieldEmpty($fieldValue) {
        return $this->dataValidator->fieldEmpty($fieldValue);
    }

    private function returnJson($data) {
        return json_encode($data);
    }

    private function validateJson($data) {
        try {
            $jsonData = json_decode($data);
            if($jsonData == NULL && $data != NULL) {
                throw new Exception();
            }
            return $jsonData;
        } catch (Exception $e) {
            throw new BadRequestException(new ApiError("410", "Expecting a valid JSON body"));
        }
    }

    private function validateContentType($request) {
//        if($request->getContentType() != "application/json") {
//            Slim::getInstance()->halt(415);
//        }
    }

    private function setResponseHeaders($response) {
        $response->header("Content-type", "application/json");
        $response->header("Access-Control-Allow-Origin","*");
        $response->header("Access-Control-Allow-Methods", "PUT, GET, POST, DELETE, OPTIONS");
    }


}

class DataValidator {

    public static $errors = array(
        "401" => "Invalid or missing query URL parameter provided",
        "402" => "Invalid or missing query parameter",
        "411" => "Request Body validation error"
    );

    public function fieldEmpty($field) {
        return (!isset($field)
            || is_null($field)
            || $field===""
            || (is_array($field) && sizeof($field) == 0));
    }

    public function validateData($fieldName, $source, $dataType, $errorCode, $mandatory, $defaultValue) {
        $value = (isset($source[$fieldName])?$source[$fieldName]:NULL);
        if($this->fieldEmpty($value)) {
            $value = $defaultValue;
        }
        // Check for mandatory field.
        if($mandatory && $this->fieldEmpty($value)) {
            $apiError = new ApiError($errorCode, self::$errors[$errorCode]);
            if($dataType === "ARRAY") {
                $apiError->addDetailError("mandarory_field", "Mandatory field is missing, the array is empty or not provided", $fieldName);
            } else {
                $apiError->addDetailError("mandarory_field", "Mandatory field is missing", $fieldName);
            }
            throw new BadRequestException($apiError);
        }
        // Check and convert data type.
        if(!$this->fieldEmpty($value)) {
            switch ($dataType) {
                CASE "INT":
                    if(strval($value) != strval(intval($value))) {
                        $apiError = new ApiError($errorCode, self::$errors[$errorCode]);
                        $apiError->addDetailError("invalid_format", "[$value] is not a valid Integer", $fieldName);
                        throw new BadRequestException($apiError);
                    }
                    $value = (int) $value;
                    break;
                CASE "FLOAT":
                    if(strval($value) != strval(floatval($value))) {
                        $apiError = new ApiError($errorCode, self::$errors[$errorCode]);
                        $apiError->addDetailError("invalid_format", "[$value] is not a valid Float", $fieldName);
                        throw new BadRequestException($apiError);
                    }
                    $value = (float) $value;
                    break;
                CASE "BOOLEAN":
                    if(strval($value) != strval(intval($value)) || ($value != 0 && $value != 1)) {
                        $apiError = new ApiError($errorCode, self::$errors[$errorCode]);
                        $apiError->addDetailError("invalid_format", "[$value] is not a valid Boolean (0: false, 1: true)", $fieldName);
                        throw new BadRequestException($apiError);
                    }
                    $value = (int) $value;
                    break;
                CASE "DATE":
                    $explodedDate = explode('-', $value);
                    if (sizeof($explodedDate) != 3
                        || strlen($explodedDate[0]) != 4
                        || !checkdate($explodedDate[1], $explodedDate[2], $explodedDate[0])) {

                        $apiError = new ApiError($errorCode, self::$errors[$errorCode]);
                        $apiError->addDetailError("invalid_format", "[$value] is not a valid date, expecting date with format YYYY-MM-DD", $fieldName);
                        throw new BadRequestException($apiError);
                    }
                    break;
                CASE "DATETIME":
                    $d = DateTime::createFromFormat('Y-m-d H:i:s', $value);
                    if(!$d || $d->format('Y-m-d H:i:s') != $value) {
                        $apiError = new ApiError($errorCode, self::$errors[$errorCode]);
                        $apiError->addDetailError("invalid_format", "[$value] is not a valid datetime, expecting date with format YYYY-MM-DD HH:MM:SS", $fieldName);
                        throw new BadRequestException($apiError);
                    }
                    break;
                CASE "EMAIL":
                    if(!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $apiError = new ApiError($errorCode, self::$errors[$errorCode]);
                        $apiError->addDetailError("invalid_format", "[$value] is not a valid RFC 822 compliant email address...", $fieldName);
                        throw new BadRequestException($apiError);
                    }
                    break;
                CASE "ARRAY":
                    if(!is_array($value)) {
                        $apiError = new ApiError($errorCode, self::$errors[$errorCode]);
                        $apiError->addDetailError("invalid_format", "[$value] is not a valid JSON Array...", $fieldName);
                        throw new BadRequestException($apiError);
                    }
                    break;

            }
        }
        return $value;
    }
}

class ApiException extends Exception {

    private $apiError;

    public function __construct($apiError)
    {
        parent::__construct("Api error happened", 0, NULL);
        $this->apiError = $apiError;

    }

    /**
     * @return mixed
     */
    public function getApiError()
    {
        return $this->apiError;
    }
}

class EntityNotFoundException extends Exception {

    private $entityName;
    private $id;

    public function __construct($entityName, $id)
    {
        parent::__construct("Api error happened", 0, NULL);
        $this->entityName = $entityName;
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getEntityName()
    {
        return $this->entityName;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }
}

class UnauthorizedAccessException extends Exception {

    public function __construct($message)
    {
        parent::__construct($message, 0, NULL);
    }
}

class BadRequestException extends ApiException {

    public function __construct($apiError)
    {
        parent::__construct($apiError);
    }
}

class ApiError {

    public $code;
    public $description;
    private $errors;

    /**
     * ApiError full constructor.
     * @param $errorCode
     * @param $errorDescription
     */
    public function __construct($errorCode, $errorDescription)
    {
        $this->code = $errorCode;
        $this->description = $errorDescription;
        $this->errors = array();
    }


    public function addDetailError($errorCode, $errorDescription, $field) {
        $this->errors[] = new ApiErrorDetail($errorCode, $errorDescription, $field);
    }

    public function to_json() {
        return get_object_vars($this);
    }
}

class ApiErrorDetail {

    public $code;
    public $description;
    public $field;

    /**
     * ApiErrorDetail full constructor.
     * @param $errorCode
     * @param $errorDescription
     * @param $field
     */
    public function __construct($errorCode, $errorDescription, $field = "")
    {
        $this->code = $errorCode;
        $this->description = $errorDescription;
        $this->field = $field;
    }
}

?>