<?php

namespace App\Controllers;

use App\Models\MahasiswaModel;
use CodeIgniter\RESTful\ResourceController;

class Mahasiswa extends ResourceController
{
    protected $mahasiswaModel, $db, $builder;
    protected $format =  'json';

    public function __construct()
    {
        $this->mahasiswaModel = new MahasiswaModel();
        $this->db = \Config\Database::connect();
        $this->builder = $this->db->table('mahasiswa');
        $this->validation = \Config\Services::validation();
    }

    public function index($id = null)
    {
        $count = $id === null ? $this->builder->countAll() : $this->builder->where('id', $id)->countAllResults();

        $data = [
            'status' => "success",
            'data' => $this->mahasiswaModel->getMahasiswa($id),
            'count' => $count,
        ];

        if ($id == null) {
            return $this->response->setJSON($data);
        }

        if ($count == 0) {
            $this->response->setStatusCode(404);
            $data = [
                'statusCode' => 404,
                'status' => 'fail',
                'message' => "Data mahasiswa dengan ID $id tidak ditemukan",
            ];
        }

        return $this->response->setJSON($data);
    }

    public function create()
    {
        if (!$this->validate([
            'email' => [
                'rules' => 'required|valid_email|is_unique[mahasiswa.email,id,{id}]',
                'errors' => [
                    'required' => '{field} mahasiswa harus diisi.',
                    'valid_email' => '{field} tidak valid.',
                    'is_unique' => '{field} tidak boleh sama.',
                ],
            ],
            'nim' => [
                'rules' => 'required|is_unique[mahasiswa.nim,id,{id}]|max_length[7]',
                'errors' => [
                    'required' => '{field} mahasiswa harus diisi.',
                    'is_unique' => '{field} tidak boleh sama.',
                    'max_length' => 'Panjang {field} tidak boleh lebih dari 7.',
                ],
            ],
            'fullname' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} mahasiswa harus diisi.',
                ],
            ],
            'userImage' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} mahasiswa harus diisi.',
                ],
            ],
        ])) {
            $this->response->setStatusCode(400);
            $data = [
                'statusCode' => 400,
                'status' =>  'fail',
                'message' => $this->validation->getErrors(),
            ];
            return $this->response->setJSON($data);
        }
        $return = $this->mahasiswaModel->save([
            'email' => $this->request->getVar('email'),
            'nim' => $this->request->getVar('nim'),
            'fullname' => $this->request->getVar('fullname'),
            'user_image' => $this->request->getVar('userImage'),
        ]);

        $this->response->setStatusCode(200);
        $data = [
            'statusCode' => 200,
            'status' => 'success',
            'message' => 'Mahasiswa berhasil ditambahkan!',
        ];
        return $this->response->setJSON($data);
    }

   public function modified($id = null)
    {
        $data = $this->request->getRawInput();

        $count = $this->builder->where('id', $id)->countAllResults();
        if ($count == 0) {
            $this->response->setStatusCode(404);
            $data = [
                'statusCode' => 404,
                'status' => "fail",
                'message' => "Mahasiswa gagal diubah. Id $id tidak ditemukan.",
            ];
            return $this->response->setJSON($data);
        }

        if (!$this->validate([
            'email' => [
                'rules' => "required|valid_email|is_unique[mahasiswa.id !=$id AND email=]", 
                'errors' => [
                    'required' => '{field} mahasiswa harus diisi',
                    'valid_email' => '{field} tidak valid.',
                    'is_unique' => '{field} tidak boleh sama.'
                ],
            ],
            'nim' => [
                'rules' => "required|is_unique[mahasiswa.id !=$id AND nim=]|max_length[7]",
                'errors' => [
                    'required' => '{field} mahasiswa harus diisi',
                    'is_unique' => '{field} tidak boleh sama.',
                    'max_length' => 'Pandang {field} tidak boleh lebih dari 7.'
                ],
            ],
            'fullname' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} mahasiswa harus diisi',
                ],
            ],
            'userImage' => [
                'rules' => 'required',
                'errors' => [
                    'required' => '{field} mahasiswa harus diisi',
                ],
            ],
        ])) {
            $this->response->setStatusCode(400);
            $data = [
                'statusCode' => 400,
                'status' => "fail",
                'message' => $this->validation->getErrors(),
            ];
            return $this->response->setJSON($data);
        }

        $return = $this->mahasiswaModel->update($id, [
            'email' => $data['email'],
            'nim' => $data['nim'],
            'fullname' => $data['fullname'],
            'userImage' => $data['userImage'],
        ]);
        if ($return == true) {
            $this->response->setStatusCode(200);
            $data = [
                'status' => "success",
                'message' => "Mahasiswa berhasil diubah",
            ];
            return $this->response->setJSON($data);
        }
    }

    public function remove($id=null)
    {
        $this->mahasiswaModel->delete($id);
        $this->response->setStatusCode(200);
        $data = [
            'statusCode' => 200,
            'status' => 'success',
            'message' => 'Data mahasiswa berhasil dihapus!',
        ];
        return $this->response->setJSON($data);
    }
}