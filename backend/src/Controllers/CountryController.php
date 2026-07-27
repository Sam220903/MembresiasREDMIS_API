<?php
class CountryController{
    private $service;
    
    public function __construct(CountryService $service){
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
                $this->listCountries();
                break;
            case 'PUT':
                $this->updateCountry($id, $data, true); // true = requires full update
                break;
            case 'PATCH':
                $this->updateCountry($id, $data, false); // false = partial update
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
                $this->listCountries();
                break;
            case 'POST':
                $this->createCountry($data);
                break;
            default:
                header('HTTP/1.1 405 Method Not Allowed');
                echo json_encode(['message' => 'Method not allowed']);
                break;
        }
    }
    
    public function listCountries(){
        $countries = $this->service->getAllCountries();

        $countriesArray = array_map(fn(Country $country) => $country->toArray(), $countries);
        $countriesArray = TypeCaster::castRows($countriesArray);

        header('Content-Type: application/json');
        echo json_encode($countriesArray);
    }
    public function createCountry($data){
        if (!isset($data['name'])) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['message' => 'Missing required fields']);
            return;
        }

        $newCountry = new Country(null, $data['name']);
        $country = $this->service->createCountry($newCountry);
        if ($country) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Country created successfully']);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['message' => 'Error creating the country']);
        }
    }
    private function updateCountry($id, $data, bool $isFullUpdate){
        // The id is always required to know which record to update; it comes from the route
        if (!isset($id) || !is_numeric($id)) {
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['message' => 'Missing id of the country to update']);
            return;
        }

        $id = (int)$id;
        $currentCountry = $this->service->getCountryById($id);

        if (!$currentCountry) {
            header('HTTP/1.1 404 Not Found');
            echo json_encode(['message' => 'Country not found']);
            return;
        }

        if ($isFullUpdate) {
            // PUT: requires all fields since it replaces the entire resource
            if (!isset($data['name'])) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['message' => 'Missing required fields for the full update']);
                return;
            }
            $currentCountry->setName($data['name']);
        } else {
            // PATCH: requires at least one field, only updates what was sent
            if (!isset($data['name'])) {
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['message' => 'You must send at least one field to update']);
                return;
            }
            $currentCountry->setName($data['name']);
        }

        $updated = $this->service->updateCountry($currentCountry);

        if ($updated) {
            header('Content-Type: application/json');
            echo json_encode(['message' => 'Country updated successfully']);
        } else {
            header('HTTP/1.1 500 Internal Server Error');
            echo json_encode(['message' => 'Error updating the country']);
        }
    }
}
