<?php

namespace Controllers;

use Models\ClientModel;
use PDO;

class ClientController extends Controller
{
    private ClientModel $clientModel;

    public function __construct(PDO $database)
    {
        parent::__construct($database);
        $this->clientModel = new ClientModel($database);
    }

    public function index()
    {
        $clients = $this->clientModel->getAllClients();
        
        echo $this->render('client.html.twig', [
            'clients' => $clients,
            'title' => 'Liste des clients'
        ]);
    }

    public function create()
    {
        return $this->render('createclient.html.twig');
    }

    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'Nom' => $_POST['Nom'],
                'Prenom' => $_POST['Prenom'],
                'Email' => $_POST['Email'],
                'Telephone' => $_POST['Telephone'],
                'Entreprise' => $_POST['Entreprise'],
                'Adresse' => $_POST['Adresse']
            ];

            if ($this->clientModel->create($data)) {
                header('Location: ' . BASE_URL . '/clients');
                exit();
            }
        }
        
        header('Location: ' . BASE_URL . '/clients/create');
        exit();
    }

    public function edit($id)
    {
        $client = $this->clientModel->getClientById($id);
        if (!$client) {
            header('Location: ' . BASE_URL . '/clients');
            exit();
        }

        $this->render('editclient.html.twig', [
            'client' => $client,
            'title' => 'Modifier le client'
        ]);
    }

    public function update($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'id_client' => $id,
                'Nom' => $_POST['Nom'],
                'Prenom' => $_POST['Prenom'],
                'Email' => $_POST['Email'],
                'Telephone' => $_POST['Telephone'],
                'Entreprise' => $_POST['Entreprise'],
                'Adresse' => $_POST['Adresse']
            ];

            if ($this->clientModel->update($data)) {
                header('Location: ' . BASE_URL . '/clients');
                exit();
            }
        }
        header('Location: ' . BASE_URL . '/clients/edit/' . $id);
        exit();
    }

    public function delete($id)
    {
        if ($this->clientModel->delete($id)) {
            header('Location: ' . BASE_URL . '/clients');
            exit();
        }
    }
}