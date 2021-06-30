<?php

declare(strict_types=1);

namespace App\Controller;

class NoteController extends AbstractController
{
    public function createAction()
    {
        if($this->request->hasPost()) {
            $noteData = [
                'title' => $this->request->postParam('title'),
                'description' => $this->request->postParam('description')
            ];
            $this->database->createNote($noteData);
            $this->redirect('/',['before' => 'created']);

        }

        $this->view->render('create');
    }

    public function showAction()
    {
        $noteId = (int) $this->request->getParam('id');

        if(!$noteId) {
            $this->redirect('/',['error' => 'missingNoteId']);
        }

        try {
            $note = $this->database->getNote($noteId);
        } catch (NotFoundException $exception) {
            $this->redirect('/',['error' => 'NoteNotFound']);
        }
        $viewParams = [
            'note' => $note
        ];

        $this->view->render('show', $viewParams ?? []);
    }

    public function listAction()
    {
        $viewParams = [
            'notes' => $this->database->getNotes(),
            'before' => $this->request->getParam('before'),
            'error' => $this->request->getParam('before')
        ];

        $this->view->render('list', $viewParams ?? []);
    }

    public function editAction()
    {
        $noteId = (int) $this->request->getParam('id');
        if(!$noteId) {
            $this->redirect('/',['error' => 'missingNoteId']);
        }
        $this->view->render('edit');
    }
}