<?php

namespace Tests\Unit;

use App\Models\DetalleOrden;
use App\Services\AbonosProductosService;
use PHPUnit\Framework\TestCase;

class AbonosProductosServiceTest extends TestCase
{
    public function test_anticipo_por_porcentaje_y_no_elegible(): void
    {
        $service=new AbonosProductosService();
        $camisa=new DetalleOrden(['cantidad'=>1,'subtotal'=>'15.00','descuento'=>'0.00',
            'permite_anticipo'=>true,'tipo_anticipo'=>'porcentaje','valor_anticipo'=>'60.00']);
        $soda=new DetalleOrden(['cantidad'=>1,'subtotal'=>'1.00','descuento'=>'0.00',
            'permite_anticipo'=>false]);
        $this->assertSame(900,$service->minimo($camisa));
        $this->assertSame(100,$service->minimo($soda));
    }

    public function test_monto_fijo_por_unidad_y_limite_de_precio_neto(): void
    {
        $service=new AbonosProductosService();
        $dos=new DetalleOrden(['cantidad'=>2,'subtotal'=>'30.00','descuento'=>'0.00',
            'permite_anticipo'=>true,'tipo_anticipo'=>'monto_fijo','valor_anticipo'=>'10.00']);
        $this->assertSame(2000,$service->minimo($dos));
        $conDescuento=new DetalleOrden(['cantidad'=>1,'subtotal'=>'15.00','descuento'=>'8.00',
            'permite_anticipo'=>true,'tipo_anticipo'=>'monto_fijo','valor_anticipo'=>'10.00']);
        $this->assertSame(700,$service->minimo($conDescuento));
    }

    public function test_redondeo_anticipo_hacia_arriba(): void
    {
        $service=new AbonosProductosService();
        $d=new DetalleOrden(['cantidad'=>1,'subtotal'=>'1.01','descuento'=>'0.00',
            'permite_anticipo'=>true,'tipo_anticipo'=>'porcentaje','valor_anticipo'=>'60.00']);
        $this->assertSame(61,$service->minimo($d));
    }
}
