<?php

namespace Iconet;

class InboxController
{

    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function renderInbox(): string
    {
        $inbox = $this->getDummyContent(); // TODO content decrypted on Server, could be done on client later
        $result = "";
        foreach($inbox as $content) {
            $result .= (new EmbeddedExperience($content['format'], $content['content']))->render();
        }
        return $result;
    }


    /**
     * @return string[][]
     */
    private function getDummyContent(): array
    {
        return [
            ['content' => 'This content will not be seen by the template', 'format' => '/iconet/formats/empty'],
            ['content' => 'This content will not be seen by the template', 'format' => '/iconet/formats/no-template'],
            ['content' => 'Content will not be seen by the template', 'format' => '/iconet/formats/empty-skeleton'],
            ['content' => 'This content will not be seen by the template', 'format' => '/iconet/formats/empty-styled'],
            ['content' => 'This format does not need content', 'format' => '/iconet/formats/static'],
            ['content' => 'This content is injected into the template', 'format' => '/iconet/formats/static'],
            ['content' => 'Different content for the same template', 'format' => '/iconet/formats/static'],
            ['content' => 'Content requested through tunnel', 'format' => '/iconet/formats/request-content'],
            ['content' => 'I am evil and try to break the parser', 'format' => '/iconet/formats/evil-html'],
            ['content' => 'I am evil and try to redirect', 'format' => '/iconet/formats/evil-redirect'],
            ['content' => 'I am evil and try to load images', 'format' => '/iconet/formats/evil-image'],
            ['content' => 'I am evil and try to delete the csp', 'format' => '/iconet/formats/evil-remove-csp'],
            ['content' => 'I am requesting content w/o permit', 'format' => '/iconet/formats/evil-request-content'],
            ['content' => 'This content is handed to the template', 'format' => '/iconet/formats/evil-inbox'],
        ];
    }
}