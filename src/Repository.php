<?php

namespace App;

class Repository
{
    private const PATH = __DIR__ . '/../repository/users';

    public function add(array $data)
    {
        $id = uniqid();
        $data['id'] = $id;
        $encodedData = "\n" . json_encode($data);
        file_put_contents(self::PATH, $encodedData, FILE_APPEND);
    }

    public function all()
    {
        $json = file_get_contents(self::PATH);
        $dataStrings = explode("\n", $json);
        $data = array_map(fn ($string) => json_decode($string), $dataStrings);
        return $data;
    }

    public function get($id)
    {
        $allData = $this->all();
        $data = collect($allData)->firstWhere('id', $id);
        return $data;
    }

    public function save($data)
    {
        $id = $data->id;
        $allData = $this->all();
        $currentData = $this->get($id);
        $indexOfData = array_search($currentData, $allData);
        $allData[$indexOfData] = $data;
        $json = array_map(fn ($item) => json_encode($item), $allData);
        file_put_contents(self::PATH, implode("\n", $json));
    }
}
