<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\DB;
use XMLReader;

class XMLParseController extends Controller
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

    public function getXML () 
    {
        try {
            $savePath = storage_path('../resources/xml/catalog.xml');
    
            foreach($this->categories as $cat) {
                $savePath = storage_path('../resources/xml/cat'.$cat.'.xml');
                $url = 'https://book24.ru/partner/tools/xml-download/?section-id='.
                    $cat.
                    '&partnerId=5003330&utm_source=affiliate&utm_medium=cpa&utm_campaign=5003330';
                $fileContents = file_get_contents($url);
                file_put_contents($savePath, $fileContents);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Произошла ошибка при импорте книг в базу данных: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function parseXML() 
    {
        try {
            $books = [];
            foreach($this->categories as $cat) {
                $path = '../resources/xml/cat' . $cat . '.xml';
                $reader = new XMLReader();
                $reader->open($path);
    
                while ($reader->read()) {
                    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'offer') {
                        $book = [
                            'id' => $reader->getAttribute('id'),
                            'name' => '',
                            'img' => '',
                            'author' => '',
                            'description' => '',
                            'category' => '',
                            'ISBN' => '',
                        ];
    
                        while ($reader->read()) {
                            if ($reader->nodeType == XMLReader::ELEMENT) {
                                switch ($reader->name) {
                                    case 'name':
                                        $reader->read();
                                        $book['name'] = $reader->value;
                                        break;
                                    case 'author':
                                        $reader->read();
                                        $book['author'] = $reader->value;
                                        break;
                                    case 'image':
                                        $reader->read();
                                        $book['img'] = $reader->value;
                                        break;
                                    case 'description':
                                        $reader->read();
                                        $book['description'] = $reader->value;
                                        break;
                                    case 'categoryId':
                                        $reader->read();
                                        $book['category'] = $reader->value;
                                        break;
                                    case 'ISBN':
                                        $reader->read();
                                        $book['ISBN'] = $reader->value;
                                        break;
                                }
                            } else if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->name == 'offer') {
                                if (!empty($book['ISBN'])) {
                                    $books[] = $book;
                                    $book = [
                                        'id' => $reader->getAttribute('id'),
                                        'name' => '',
                                        'img' => '',
                                        'author' => '',
                                        'description' => '',
                                        'category' => '',
                                        'ISBN' => '',
                                    ];
                                }
                                break;
                            }
                        }
                    } 
                } 
                $reader->close();
            }
    
            $chunked_books = array_chunk($books, 5000);
            $i = 1;
            foreach ($chunked_books as $chunk) {
                $filename = 'books_' . $i . '.json';
                $json_books = json_encode($chunk, JSON_UNESCAPED_UNICODE);
                file_put_contents(public_path($filename), $json_books);
                $i++;
            }
    
            return response()->json([
                'message' => 'Книги успешно добавлены.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Произошла ошибка при добавлении книг. Обратитесь к администратору сайта.',
            ], 500);
        }
    }

    public function importBooks()
    {
        try {
            set_time_limit(1000);
    
            $dir = scandir(public_path());
            $book_files = array_filter($dir, function ($file) {
                return strpos($file, 'books_') === 0 && strpos($file, '.json') !== false;
            });
    
            sort($book_files);
    
            foreach ($book_files as $book_file) {
                $path = public_path($book_file);
                $contents = file_get_contents($path);
                $books = json_decode($contents, true);
            
                foreach ($books as $book) {
                    $existing_book = Book::where('id', $book['id'])->first();
                    if (!$existing_book) {
                        Book::firstOrCreate([
                            'id' => $book['id'],
                            'name' => $book['name'],
                            'img' => $book['img'],
                            'author' => $book['author'],
                            'description' => $book['description'],
                            'category' => $book['category'],
                            'ISBN' => $book['ISBN'],
                        ]);
                    }
                }
            
                unlink($path);
            }
    
            return response()->json([
                'message' => 'Книги успешно импортированы в базу данных.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Произошла ошибка при импорте книг в базу данных: ' . $e->getMessage(),
            ], 500);
        }
    }
}
