<?php
class UniversityController{
    private $service;
    
    public function __construct(UniversityService $service){
        $this->service = $service;
        // Add the payload once that part is finished
    }
    public function handleRequest($method, $data, $id = null)
    {
        if ($id) {
            $this->processResourceRequest($method, $id, $data);
        } else {
            $this->processCollectionRequest($method, $data);
        }
    }

    public function processResourceRequest($method, $id, $data)
    {
        switch ($method) {
            case 'GET':
                $this->listUniversities();
                break;
            case 'PUT':
                $this->updateUniversity($id, $data, true); // true = requires full update
                break;
            case 'PATCH':
                $this->updateUniversity($id, $data, false); // false = partial update
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }
    }

    public function processCollectionRequest($method, $data)
    {
        switch ($method) {
            case 'GET':
                $this->listUniversities();
                break;
            case 'POST':
                $this->createUniversity($data);
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }
    }
    
    public function listUniversities(){
        $universities = $this->service->getAllUniversities();

        $universitiesArray = array_map(fn(University $university) => $university->toArray(), $universities);
        $universitiesArray = TypeCaster::castRows($universitiesArray);

        header('Content-Type: application/json');
        echo json_encode($universitiesArray);
    }
    private function createUniversity($data){
        if (!isset($data['name']) || !isset($data['country_id'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['message' => 'Missing required fields']);
            return;
        }

        $newUniversity = new University(null, $data['name'], (int)$data['country_id']);
        $university = $this->service->createUniversity($newUniversity);
        if ($university) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'University created successfully']);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['message' => 'Error creating the university']);
        }
    }
    private function updateUniversity($id, $data, bool $isFullUpdate){
        // The id is always required to know which record to update; it comes from the route
        if (!isset($id) || !is_numeric($id)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['message' => 'Missing id of the university to update']);
            return;
        }

        $id = (int)$id;
        $currentUniversity = $this->service->getUniversityById($id);

        if (!$currentUniversity) {
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['message' => 'University not found']);
            return;
        }

        if ($isFullUpdate) {
            // PUT: requires all fields since it replaces the entire resource
            if (!isset($data['name']) || !isset($data['country_id'])) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['message' => 'Missing required fields for the full update']);
                return;
            }
            $currentUniversity->setName($data['name']);
            $currentUniversity->setCountryId((int)$data['country_id']);
        } else {
            // PATCH: requires at least one field, only updates what was sent
            if (!isset($data['name']) && !isset($data['country_id'])) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['message' => 'You must send at least one field to update']);
                return;
            }
            if (isset($data['name'])) {
                $currentUniversity->setName($data['name']);
            }
            if (isset($data['country_id'])) {
                $currentUniversity->setCountryId((int)$data['country_id']);
            }
        }

        $updated = $this->service->updateUniversity($currentUniversity);

        if ($updated) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'University updated successfully']);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['message' => 'Error updating the university']);
        }
    }
}