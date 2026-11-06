<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\ExcelService;
use ReflectionClass;

class ExcelServiceTest extends TestCase
{
    public function test_excel_service_instantiation_and_structure()
    {
        $service = new ExcelService();
        $this->assertInstanceOf(ExcelService::class, $service);

        $reflector = new ReflectionClass($service);
        $this->assertTrue($reflector->hasMethod('export'));
        $this->assertTrue($reflector->hasMethod('import'));
        $this->assertTrue($reflector->hasMethod('filterColumnsByYear'));
        $this->assertTrue($reflector->hasMethod('getFlattenedIndicators'));
        $this->assertTrue($reflector->hasMethod('buildHeader'));
        $this->assertTrue($reflector->hasMethod('writeDataRows'));
        $this->assertTrue($reflector->hasMethod('writeFooterTotal'));
        $this->assertTrue($reflector->hasMethod('applyStylingAndWidths'));
    }

    public function test_filter_columns_by_year()
    {
        $service = new ExcelService();
        $reflector = new ReflectionClass($service);
        $method = $reflector->getMethod('filterColumnsByYear');
        $method->setAccessible(true);

        $columns = [
            ['name' => 'No'],
            ['name' => 'Nama Indikator'],
            ['name' => '2021'],
            ['name' => '2022'],
            ['name' => '2023'],
            ['name' => '2024'],
        ];

        $filtered = $method->invoke($service, $columns, 2022, 2023);

        $names = array_map(fn($c) => $c['name'], $filtered);
        $this->assertEquals(['No', 'Nama Indikator', '2022', '2023'], $names);
    }
}
