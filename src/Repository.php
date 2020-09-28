<?php

class Repository
{
	const PATH = __DIR__ . '/../repository/users';

	public function add($data)
	{
		$id = uniqid();
		$data['id'] = $id;
		$encodedData = json_encode($data) . "\n";
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
		$data = array_filter($allData, fn ($item) => $item->id === $id);
		return $data;
	}
}
