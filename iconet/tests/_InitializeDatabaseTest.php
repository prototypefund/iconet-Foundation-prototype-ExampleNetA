<?php

require_once "config/config.php";


use Iconet\IconetOutbox;
use Iconet\UserManager;
use PHPUnit\Framework\TestCase;

/**
 * This is just a convenient way to initialize the database with users and posts.
 */
class _InitializeDatabaseTest extends TestCase
{

    private \Iconet\User $alice;
    private \Iconet\User $bob;
    private User $aliceNative;
    private User $bobNative;


    protected function setUp(): void
    {
        parent::setUp();
        \Iconet\Database::singleton()->clearTables();
        Database::singleton()->clearTables();
    }

    public function test_clearTables(): void
    {
        \Iconet\Database::singleton()->clearTables();
        self::assertTrue(true);
    }


    public function test_initialize(): void
    {
        if(!UserManager::addNewUser("alice")) {
            self::fail("Could not create neta user alice");
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
            self::fail("Could not create neta user bob");
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

        $this->alice = \Iconet\User::fromUsername('alice');
        $this->bob = \Iconet\User::fromUsername('bob');
        $this->bob->addContact($this->alice);
        $this->alice->addContact($this->bob);

        $this->aliceNative = new User('alice');
        $this->bobNative = new User('bob');
        $this->aliceNative->sendFriendRequest('bob');
        $this->bobNative->acceptFriendRequest($this->aliceNative);
    }

    public function test_createPost(): void
    {   
        global $con;

        $this->test_initialize();

        $postBody = "Content by Unittest";

        $netaPostId = (new Post($con, $this->bob->username))->submitPost($postBody, 'alice');

        (new IconetOutbox($this->bob))->createPost(['content' => $postBody, 'username' => $this->bob->username, 'id' => $netaPostId],
            "/iconet/formats/post-like-comment-neta/manifest.json");
    }

    public function test_createAllPostFormats(): void
    {
        $this->test_initialize();

        array_map(
            fn($post) => (new IconetOutbox($this->bob))->createPost(
                ['content' => $post['content'], 'username' => 'alice'],
                $post['formatId']
            ),
            [
                [
                    'content' => 'This content will not be seen by the template',
                    'formatId' => '/iconet/formats/empty/manifest.json'
                ],
                [
                    'content' => 'This content will not be seen by the template',
                    'formatId' => '/iconet/formats/no-template/manifest.json'
                ],
                [
                    'content' => 'Content will not be seen by the template',
                    'formatId' => '/iconet/formats/empty-skeleton/manifest.json'
                ],
                [
                    'content' => 'This content will not be seen by the template',
                    'formatId' => '/iconet/formats/empty-styled/manifest.json'
                ],
                [
                    'content' => 'This format does not need content',
                    'formatId' => '/iconet/formats/static-no-js/manifest.json'
                ],
                [
                    'content' => 'This content is injected into the template',
                    'formatId' => '/iconet/formats/static/manifest.json'
                ],
                [
                    'content' => 'Different content for the same template',
                    'formatId' => '/iconet/formats/static/manifest.json'
                ],
                [
                    'content' => 'I am evil and try to break the parser',
                    'formatId' => '/iconet/formats/evil-html/manifest.json'
                ],
                [
                    'content' => 'I am evil and try to redirect',
                    'formatId' => '/iconet/formats/evil-redirect/manifest.json'
                ],
                [
                    'content' => 'I am evil and try to load images',
                    'formatId' => '/iconet/formats/evil-image/manifest.json'
                ],
                [
                    'content' => 'I am evil and try to delete the csp',
                    'formatId' => '/iconet/formats/evil-remove-csp/manifest.json'
                ],
                [
                    'content' => 'This content is handed to the template',
                    'formatId' => '/iconet/formats/evil-inbox/manifest.json'
                ],
                [
                    'content' => 'This format can make requests to an external resource',
                    'formatId' => '/iconet/formats/allowed-source/manifest.json'
                ],
                [
                    'content' => "\n## Title\n**bold** ~~deleted~~\n\n> Quote\n\n```js\nimport { marked } from 'marked';\nimport { parentPort } from 'worker_threads';\n\nparentPort.on('message', (markdownString) =\u003e {\n  parentPort.postMessage(marked.parse(markdownString));\n});\n```\n\n- Item 1\n   - Item 1.1\n   - Item 1.2\n- Item 2\n- Item 3\n\n| Item         | Price     | # In stock |\n|--------------|-----------|------------|\n| Juicy Apples | 1.99      | 7          |\n| Bananas      | 1.89      | 5234       |\n",
                    'formatId' => '/iconet/formats/markdown/manifest.json'
                ],
                [
                    'content' => 'You may comment this post',
                    'formatId' => '/iconet/formats/post-like-comment/manifest.json'
                ],
            ]
        );
    }
}
