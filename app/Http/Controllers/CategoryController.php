<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use XMLReader;

class CategoryController extends Controller
{
    protected $categories = [
        '1426',
        '3351',
        '4560',
        '1416',
        '1804',
        '1159',
        '1529',
        '1671',
        '1592'
    ];
    
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    public function parse() 
    {
        set_time_limit(1000);
        foreach ($this->categories as $cat) {
            $path = '../resources/xml/cat' . $cat . '.xml';
            $reader = new XMLReader();
            $reader->open($path);
                
            while ($reader->read()) {
                if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'categories') {
                    while ($reader->read()) {
                        if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'category') {
                            Category::firstOrCreate([
                                'id' => $reader->getAttribute('id'),
                                'category' => $reader->readString()
                            ]);
                        }
                    }
                }
            }
        }
    }
}