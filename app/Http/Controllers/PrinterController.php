<?php

namespace App\Http\Controllers;

use App\Models\RowItem;
use Illuminate\Http\Request;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\Printer;

class PrinterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $printableData = json_decode(json_encode($request->print_data));

            $printer = $this->printReceipt($printableData);
//            $printer = $this->test();


            return response()->json($printer);
        }catch (\Throwable $th){
            return response()->json($th->getMessage());
        }
    }

    public function printReceipt($printableData)
    {
        $total = $printableData->payed_amount + $printableData->due_amount + $printableData->delivery_fee;
        $total = $total - $printableData->discount_amount;
        try {
            $connector = new NetworkPrintConnector("192.168.31.140", 9100);
        } catch (\Exception $e) {
            $notification = array(
                'message' => 'Sorry! Printer is not connected in this network......',
                'alert_type' => 'error'
            );
            return $notification;
        }
//        return $connector;

        $items = [];
        foreach ($printableData->sales as $sale){
//            $qty = $sale->sale_qty / $sale->product->convert_unit;
            $qty = (floor($sale->sale_qty / $sale->product->convert_unit) > 0 ? floor($sale->sale_qty / $sale->product->convert_unit).' '.$sale->product->high_unit_name.' ':'').($sale->sale_qty % $sale->product->convert_unit > 0 ? ($sale->sale_qty % $sale->product->convert_unit).' '.$sale->product->low_unit_name.' ':'');
            $items[] = new RowItem($sale->product->name.' ('.$qty.')', number_format($sale->amount,'2','.',','));
        }
        $discountTotal = new RowItem('Discount', number_format($printableData->discount_amount,'2','.',','));
        $dueTotal = new RowItem('Due', number_format($printableData->due_amount,'2','.',','));
        $deliveryFeeTotal = new RowItem('Delivery Fee', $printableData->delivery_fee?number_format($printableData->delivery_fee,'2','.',','):'0.00');
        $total = new RowItem('Total', number_format($total,'2','.',','));
        /* Date is kept the same for testing */
        $date = date('l jS \of F Y h:i:s A',time());


        /* Start the printer */
        $printer = new Printer($connector);
        /* Print top logo */

        /* Name of shop */

//        $printer -> selectPrintMode(Printer::MODE_FONT_B);
        $printer -> selectPrintMode();
        $printer -> setJustification(Printer::JUSTIFY_CENTER);
        $printer -> setTextSize(2, 2);
        $printer -> text("M/S.Ahmed&Sons.\n");
        $printer -> setTextSize(1, 1);
        $printer -> text("F-55, Savar Bazar, Savar, Dhaka-1340\n");
        $printer -> selectPrintMode();
        $printer -> setEmphasis(true);
        $printer -> text("Phone No. 01711234938, 01726059072 \n");
        $printer -> feed();
        $printer -> text("Order No.".$printableData->invoice_no." \n");
        $printer -> feed();

        /* Title of receipt */


        $printer -> setJustification(Printer::JUSTIFY_LEFT);
        $printer -> setEmphasis(true);
        $printer -> text("Customer Info\n");
        $printer -> setEmphasis(false);
        $printer -> setEmphasis(true);
        $printer -> text("Name: ". $printableData->customer->name. "\n");
        $printer -> text("Phone: ". $printableData->customer->phone. "\n");
        $printer -> text("Address: ". $printableData->customer->address->address. "\n");
        $printer -> setEmphasis(false);
        $printer -> feed();

        /* Items */
        $printer -> setJustification(Printer::JUSTIFY_LEFT);
        $printer -> setEmphasis(true);
        $printer -> text('------------------------------------------------');
        $printer -> text(new RowItem('Product Name', 'Price'));
        $printer -> text('------------------------------------------------');
        $printer -> setEmphasis(false);
        foreach ($items as $item) {
            $printer -> text($item);
        }
        $printer -> setEmphasis(true);
        $printer -> text('------------------------------------------------');
        $printer -> text($discountTotal);
        $printer -> text($deliveryFeeTotal);
        $printer -> setEmphasis(false);
        $printer -> feed();

        /* Tax and total */
//        $printer -> text($tax);
        $printer -> text($dueTotal);
        $printer -> text('------------------------------------------------');
//        $printer -> selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
        $printer -> setTextSize(1, 2);
        $printer -> text($total);
        $printer -> setTextSize(1, 1);
        $printer -> text('------------------------------------------------');
        $printer -> selectPrintMode();

        /* Footer */
        $printer -> feed(2);
        $printer -> setJustification(Printer::JUSTIFY_CENTER);
        $printer -> text("Thank you for shopping at M/S.Ahmed&Sons\n");
        $printer -> feed();
        $printer -> text($date . "\n");
        $printer -> feed(2);
//        sleep(5);

        /* Cut the receipt and open the cash drawer */
        $printer -> cut();
        $printer -> pulse();

        $printer -> close();

//        dd($printer);
        $notification = array(
            'message' => 'Print Success',
            'alert_type' => 'success'
        );
        return $notification;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
