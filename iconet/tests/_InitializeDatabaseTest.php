<?php

require_once "config/config.php";


use Iconet\Processor;
use Iconet\UserManager;
use PHPUnit\Framework\TestCase;

/**
 * This is just a convenient way to initialize the database with users and posts.
 */
class _InitializeDatabaseTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Database::singleton()->clearTables();
        (new \Iconet\Database)->clearTables();
    }


    public function test_initialize(): void
    {
        if(!UserManager::addNewUser("alice")) {
            self::fail("Could not create user alice");
        } else {
            Database::singleton()->registerUser(
                "Alice",
                "Abott",
                "alice",
                "a@a.a",
                md5("aaaaa"),
                date("Y-m-d H:i:s"),
                "/favicon.ico"
            );
        }
        if(!UserManager::addNewUser("bob")) {
            self::fail("Could not create user bob");
        } else {
            Database::singleton()->registerUser(
                "Bob",
                "Barley",
                "bob",
                "b@b.b",
                md5("bbbbb"),
                date("Y-m-d H:i:s"),
                "/favicon.ico"
            );
        }
        self::assertTrue(Database::singleton()->existsUser('bob'));
    }

    public function test_createPost(): void
    {
        $this->test_initialize();
        $alice = \Iconet\User::fromUsername('alice');
        $bob = \Iconet\User::fromUsername('bob');

        $bob->addContact($alice);
        (new Processor($bob))->createPost("Test Post Content", "/iconet/formats/post-like-comment");
    }

    public function test_createAllPostFormats(): void
    {
        $this->test_initialize();
        $alice = \Iconet\User::fromUsername('alice');
        $bob = \Iconet\User::fromUsername('bob');
        $bob->addContact($alice);

        array_map(fn($post) => (new Processor($bob))->createPost($post['content'], $post['formatId']), [
                [
                    'content' => 'This content will not be seen by the template',
                    'formatId' => '/iconet/formats/empty'
                ],
                [
                    'content' => 'This content will not be seen by the template',
                    'formatId' => '/iconet/formats/no-template'
                ],
                [
                    'content' => 'Content will not be seen by the template',
                    'formatId' => '/iconet/formats/empty-skeleton'
                ],
                [
                    'content' => 'This content will not be seen by the template',
                    'formatId' => '/iconet/formats/empty-styled'
                ],
                [
                    'content' => 'This format does not need content',
                    'formatId' => '/iconet/formats/static-no-js'
                ],
                [
                    'content' => 'This content is injected into the template',
                    'formatId' => '/iconet/formats/static'
                ],
                [
                    'content' => 'Different content for the same template',
                    'formatId' => '/iconet/formats/static'
                ],
                [
                    'content' => 'Content requested through tunnel',
                    'formatId' => '/iconet/formats/request-content'
                ],
                [
                    'content' => 'Resource requested through tunnel',
                    'formatId' => '/iconet/formats/request-resource'
                ],
                [
                    'content' => 'Send an interaction',
                    'formatId' => '/iconet/formats/interaction'
                ],
                [
                    'content' => 'Send an interaction',
                    'formatId' => '/iconet/formats/interaction-button'
                ],
                [
                    'content' => 'I am evil and try to break the parser',
                    'formatId' => '/iconet/formats/evil-html'
                ],
                [
                    'content' => 'I am evil and try to redirect',
                    'formatId' => '/iconet/formats/evil-redirect'
                ],
                [
                    'content' => 'I am evil and try to load images',
                    'formatId' => '/iconet/formats/evil-image'
                ],
                [
                    'content' => 'I am evil and try to delete the csp',
                    'formatId' => '/iconet/formats/evil-remove-csp'
                ],
                [
                    'content' => 'I am requesting content w/o permit',
                    'formatId' => '/iconet/formats/evil-request-content'
                ],
                [
                    'content' => 'This content is handed to the template',
                    'formatId' => '/iconet/formats/evil-inbox'
                ]
            ]
        );
    }
}
