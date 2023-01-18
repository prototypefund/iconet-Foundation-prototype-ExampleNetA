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

        $alice = \Iconet\User::fromUsername('alice');
        $bob = \Iconet\User::fromUsername('bob');
        $bob->addContact($alice);
        $alice->addContact($bob);

        $aliceNative = new User('alice');
        $bobNative = new User('bob');
        $aliceNative->sendFriendRequest('bob');
        $bobNative->acceptFriendRequest($aliceNative);
    }

    public function test_createPost(): void
    {
        $this->test_initialize();
        (new IconetOutbox($bob))->createPost(['content' => "Content by UnitTest", '$username' => "alice"],
            "/iconet/formats/markdown/manifest.json");
    }

    public function test_createAllPostFormats(): void
    {
        $this->test_initialize();

        array_map(
            fn($post) => (new IconetOutbox($bob))->createPost(
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
                ]
            ]
        );
    }
}
