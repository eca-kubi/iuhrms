<?php


use JetBrains\PhpStorm\NoReturn;

abstract class BaseAPIController
{
    abstract public function handleGetRequest(?int $id);

    #[NoReturn]
    public function handlePostRequest(array $data): void
    {
        // Not implemented
        // Send appropriate response
        $this->sendResponse(501, ['error' => 'Not Implemented', 'code' => 501, 'success' => false]);
    }

    #[NoReturn]
    public function handlePutOrPatchRequest(int $id, array $data, bool $isPatch): void
    {
        // Not implemented
        // Send appropriate response
        $this->sendResponse(501, ['error' => 'Not Implemented', 'code' => 501, 'success' => false]);
    }

    #[NoReturn]
    public function handleDeleteRequest(int $id): void
    {
        // Not implemented
        // Send appropriate response
        $this->sendResponse(501, ['error' => 'Not Implemented', 'code' => 501, 'success' => false]);
    }


    public function handleRequest(?int $id): void
    {
        // Is user logged in?
        if (!Helpers::is_logged_in()) {
            // Send a 401 response
            $this->sendResponse(401, ['error' => 'Unauthorized', 'code' => 401, 'success' => false]);
        }

        // If request is not AJAX, redirect to home page
        /*if (!Helpers::is_ajax()) {
            Helpers::redirect_to(URL_ROOT . '/');
        }*/

        if (Helpers::is_get()) {
            $this->handleGetRequest($id);
        } else if (Helpers::is_post()) {
            $this->handlePostRequest($this->getPostBody());
        } else if ((Helpers::is_put() || Helpers::is_patch()) && $id !== null) {
            $this->handlePutOrPatchRequest($id, $this->getPostBody(), Helpers::is_patch());
        } else if (Helpers::is_delete() && $id !== null) {
            $this->handleDeleteRequest($id);
        } else {
            $this->sendResponse(400, ['error' => 'Invalid request method', 'code' => 400, 'success' => false]);
        }
    }

    #[NoReturn]
    protected function sendResponse(int $statusCode, array $data): void
    {
        $message = Helpers::json_encode($data);
        Helpers::sendHttpResponse($statusCode, $message, 'application/json');
        exit;
    }

    protected function getPostBody(): array
    {
        $data = [];
        if (Helpers::get_content_type() === 'application/json') {
            // Get new Reservation data from POST data
            try {
                $data = Helpers::get_json_data();
            } catch (Exception $e) {
                // Return 400
                $message = Helpers::json_encode(['error' => 'Invalid JSON', 'code' => 400, 'success' => false]);
                Helpers::sendHttpResponse(400, $message, 'application/json');
                Helpers::log_error($e->getMessage());
            }
        } else {
            // Get new Reservation data from POST data
            $data = Helpers::get_post_data();
        }
        return $data;
    }

    protected function validateModel(Model $model, array $requiredFields=[]): array
    {
        $validator = $model->getValidator();
        $validator->validate($requiredFields);
        return $validator->getErrors();
    }

    protected function validateId(string $idField, Model $model, bool $required): void {
        $errors = $model->getValidator()->validateId($required)->getErrors();
        if (isset($errors[$idField])) {
            $modelIdErrors = $errors[$idField];
            if (isset($modelIdErrors[ValidationErrorTypeNames::REQUIRED])) {
                $this->sendResponse(400, ['error' => $modelIdErrors[ValidationErrorTypeNames::REQUIRED], 'code' => 400, 'success' => false]);
            }
            if (isset($modelIdErrors[ValidationErrorTypeNames::INVALID_TYPE])) {
                $this->sendResponse(400, ['error' => $modelIdErrors[ValidationErrorTypeNames::INVALID_TYPE], 'code' => 400, 'success' => false]);
            }
            if (isset($modelIdErrors[ValidationErrorTypeNames::DOES_NOT_EXIST])) {
                $this->sendResponse(404, ['error' => $modelIdErrors[ValidationErrorTypeNames::DOES_NOT_EXIST], 'code' => 404, 'success' => false]);
            }
        }
    }
}