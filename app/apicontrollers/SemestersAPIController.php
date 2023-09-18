<?php

class SemestersAPIController extends BaseAPIController
{
    public function handleGetRequest(?int $id): void
    {
        try {
            if ($id !== null) {
                $semester = SemesterModel::getOneById($id);
                if ($semester !== null) {
                    $this->sendResponse(200, ['semester' => $semester, 'success' => true]);
                } else {
                    $this->sendResponse(404, ['error' => 'Semester not found', 'code' => 404, 'success' => false]);
                }
            } else {
                $semesters = SemesterModel::getAll();
                $this->sendResponse(200, ['semesters' => $semesters, 'success' => true]);
            }
        } catch (Exception $e) {
            Helpers::log_error($e->getMessage());
            $this->sendResponse(500, ['error' => 'Internal Server Error', 'code' => 500, 'success' => false]);
        }
    }
}