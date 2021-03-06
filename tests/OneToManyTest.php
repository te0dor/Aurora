<?php
/**
 * Aurora - Fast and easy to use php ORM.
 *
 * @author      José Miguel Molina <hi@mvader.me>
 * @copyright   2013 José Miguel Molina
 * @link        https://github.com/mvader/Aurora
 * @license     https://raw.github.com/mvader/Aurora/master/LICENSE
 * @version     1.0.3
 * @package     Aurora
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

use \Aurora\Table;
use \Aurora\Column;
use \Aurora\Types\Int;
use \Aurora\Types\String;
use \Aurora\Query;
use \Aurora\ForeignKey;
use \Aurora\Relationship;

class OTM_User extends Table
{
    protected $user_id;
    protected $user_name;
    protected $user_mail;
    protected $user_password;
    protected $posts;
    
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
        
        $this->posts = new Relationship('Post', 'user_id', 'user_id', false);
    }
}

class Post extends Table
{
    protected $post_id;
    protected $user_id;
    protected $title;
    protected $user;
    
    protected function setup()
    {
        $this->name = 'posts';
        
        $this->post_id = new Column(new Int());
        $this->post_id->primaryKey = true;
        $this->post_id->autoIncrement = true;
        $this->user_id = new Column(new Int());
        $this->user_id->foreignKey = new ForeignKey(
            'OTO_User',
            'user_id',
            'user_id',
            'CASCADE',
            'CASCADE'
        );
        $this->title = new Column(new String(255));
        $this->title->default = '';
        
        $this->user = new Relationship('OTO_User', 'user_id', 'user_id');
    }
}

class OneToManyTest extends PHPUnit_Framework_TestCase
{
    public function testCreateTables()
    {
        $user = new OTM_User();    
        $this->assertEquals(true, $user->createTable());
        
        $post = new Post();
        $this->assertEquals(true, $post->createTable());
    }
    
    public function testInsertRow()
    {
        $user = new OTM_User();
        $user->user_name = "Bob Doe";
        $user->user_mail = 'bobdoe@bobmail.com';
        $user->user_password = 'supersecret';
        $this->assertEquals(true, $user->save());
        
        $post1 = new Post();
        $post2 = new Post();
        $post3 = new Post();
        
        $post1->user_id = $user->user_id;
        $post1->title = 'Post 1';
        $this->assertEquals(true, $post1->save());
        
        $post2->user_id = $user->user_id;
        $post2->title = 'Post 2';
        $this->assertEquals(true, $post2->save());
        
        $post3->user_id = $user->user_id;
        $post3->title = 'Post 3';
        $this->assertEquals(true, $post3->save());
    }
    
    public function testRelation()
    {
        $user = OTM_User::query()
            ->filterBy(array('user_name', 'Bob Doe'))
            ->first();
        $this->assertEquals(3, count($user->posts));
        $this->assertEquals('Post 3', $user->posts[2]->title);
    }
    
    public function testDropTable()
    {
        $user = new OTM_User();
        $post = new Post();
        
        $exceptionThrown = false;
        
        try {
            $this->assertEquals(true, $user->dropTable());
            $this->assertEquals(true, $post->dropTable());
        } catch (\RuntimeException $e) {
            $exceptionThrown = true;
            $this->assertEquals(true, true);
        }
        
        if ($exceptionThrown) {
            $this->assertEquals(true, $post->dropTable());
            $this->assertEquals(true, $user->dropTable());
        }
    }
}
