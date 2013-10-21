<?php

use \Aurora\Table;
use \Aurora\Column;
use \Aurora\Types\Int;
use \Aurora\Types\String;
use \Aurora\Query;
use \Aurora\ForeignKey;
use \Aurora\Relationship;

class MTM_User extends Table
{
    protected $user_id;
    protected $user_name;
    protected $user_mail;
    protected $user_password;
    protected $bookings;
    
    protected function setup()
    {
        $this->name = 'users';
        
        $this->user_id = new Column(new Int());
        $this->user_id->primaryKey = true;
        $this->user_id->autoIncrement = true;
        $this->user_name = new Column(new String(80));
        $this->user_name->unique = true;
        $this->user_name->default = '';
        $this->user_mail = new Column(new String(80));
        $this->user_password = new Column(new String(80));
        
        $this->bookings = new Relationship('Booking', 'user_id', 'user_id', false);
    }
}

class Book extends Table
{
    protected $book_id;
    protected $title;
    protected $bookings;
    
    protected function setup()
    {
        $this->name = 'books';
        
        $this->book_id = new Column(new Int());
        $this->book_id->primaryKey = true;
        $this->book_id->autoIncrement = true;
        $this->title = new Column(new String(255));
        $this->title->default = '';
        
        $this->bookings = new Relationship('Booking', 'book_id', 'book_id', false);
    }
}

class Booking extends Table
{
    protected $user_id;
    protected $book_id;
    protected $booked;
    protected $user;
    protected $book;
    
    protected function setup()
    {
        $this->name = 'bookings';
        
        $this->user_id = new Column(new Int());
        $this->user_id->primaryKey = true;
        $this->user_id->foreignKey = new ForeignKey(
            'MTM_User',
            'user_id',
            'user_id',
            'CASCADE',
            'CASCADE'
        );
        $this->book_id = new Column(new Int());
        $this->book_id->primaryKey = true;
        $this->book_id->foreignKey = new ForeignKey(
            'Book',
            'book_id',
            'book_id',
            'CASCADE',
            'CASCADE'
        );
        $this->booked = new Column(new String(20));
        $this->booked->default = '';
        
        $this->book = new Relationship('Book', 'book_id', 'book_id');
        $this->user = new Relationship('MTM_User', 'user_id', 'user_id');
    }
}

class ManyToManyTest extends PHPUnit_Framework_TestCase
{
    public function testCreateTables()
    {
        $user = new MTM_User();
        $this->assertEquals(true, $user->createTable());
        
        $book = new Book();
        $this->assertEquals(true, $book->createTable());
        
        $booking = new Booking();
        $this->assertEquals(true, $booking->createTable());
    }
    
    public function testInsertRow()
    {
        $user1 = new MTM_User();
        $user1->user_name = 'Bob';
        $user1->user_mail = 'bob@auroramail.com';
        $user1->user_password = 'supersecret';
        $this->assertEquals(true, $user1->save());
        
        $user2 = new MTM_User();
        $user2->user_name = 'Mike';
        $user2->user_mail = 'mike@auroramail.com';
        $user2->user_password = 'supersecret';
        $this->assertEquals(true, $user2->save());
        
        $book1 = new Book();
        $book1->title = 'Ponies of the 12th century';
        $this->assertEquals(true, $book1->save());
        
        $book2 = new Book();
        $book2->title = 'Here be dragons. Or not.';
        $this->assertEquals(true, $book2->save());
        
        $booking1 = new Booking();
        $booking1->user_id = $user1->user_id;
        $booking1->book_id = $book1->book_id;
        $this->assertEquals(true, $booking1->save());
        
        $booking2 = new Booking();
        $booking2->user_id = $user1->user_id;
        $booking2->book_id = $book2->book_id;
        $this->assertEquals(true, $booking2->save());
        
        $booking3 = new Booking();
        $booking3->user_id = $user2->user_id;
        $booking3->book_id = $book1->book_id;
        $this->assertEquals(true, $booking3->save());
        
        $booking4 = new Booking();
        $booking4->user_id = $user2->user_id;
        $booking4->book_id = $book2->book_id;
        $this->assertEquals(true, $booking4->save());
    }
    
    public function testRelation()
    {
        $user = MTM_User::query()
            ->filterBy(array('user_name', 'Bob'))
            ->first();
        $this->assertEquals(2, count($user->bookings));
        $this->assertEquals('Ponies of the 12th century', $user->bookings[0]->book->title);
        
        $book = Book::query()
            ->filterBy(array('title', 'Ponies of the 12th century'))
            ->first();
        $this->assertEquals(2, count($book->bookings));
        $this->assertEquals('Mike', $book->bookings[1]->user->user_name);
    }
    
    public function testDropTable()
    {
        $user = new MTM_User();
        $book = new Book();
        $booking = new Booking();
        
        try {
            $this->assertEquals(true, $user->dropTable());
            $this->assertEquals(true, $book->dropTable());
            $this->assertEquals(true, $booking->dropTable());
        } catch (\Aurora\Error\DatabaseException $e) {
            $this->assertEquals(true, true);
        }
        
        $this->assertEquals(true, $booking->dropTable());
        $this->assertEquals(true, $book->dropTable());
        $this->assertEquals(true, $user->dropTable());
    }
}