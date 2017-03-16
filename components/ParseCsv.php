<?php

namespace components;

/**
 * Work with csv
 * Class ParseCsv
 * @package components
 */
class ParseCsv
{
    /**
     * @var array
     */
    private $parsedValues = [];

    /**
     * Parse csv file to 2x array
     * @param $file
     * @return array
     */
    public function parse(string $file): ParseCsv
    {
        $row = 0;
        if (($handle = fopen($file, "rb")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $num = count($data);
                $row++;
                for ($c = 0; $c < $num; $c++) {
                    $this->parsedValues[$row][$c] = $data[$c];
                }
            }
            fclose($handle);
        }
        return $this;
    }

    /**
     * Get one column from result
     * @param $column
     * @throws \Exception
     * @return array
     */
    public function getColumn(int $column): array
    {
        $this->checkExistParsedValue();
        $result = [];
        foreach ($this->parsedValues as $value) {
            if (!isset($value[$column])) {
                throw new \Exception('Have not column');
            }
            $result[] = $value[$column];
        }
        return $result;
    }

    /**
     * Check exist parsed value
     * @throws \Exception
     */
    private function checkExistParsedValue()
    {
        if (empty($this->parsedValues)) {
            throw new \Exception('Empty result of parsing csv file');
        }
    }
}
