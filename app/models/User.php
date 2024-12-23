<?php

namespace App\Models;

use Config\Database;
use Exception;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function findUser($identifier)
    {
        try {
            // Search in mahasiswa table
            $sqlMahasiswa = "SELECT 'mahasiswa' AS type, id_mahasiswa, nim AS identifier, password, nama, email, foto_profil, angkatan, prodi_id 
                            FROM mahasiswa 
                            WHERE nim = ?";
            $stmtMahasiswa = $this->db->prepareAndExecute($sqlMahasiswa, [$identifier]);
            $resultMahasiswa = $this->db->fetchAll($stmtMahasiswa);

            if ($resultMahasiswa) {
                return $resultMahasiswa[0]; // If found in mahasiswa
            }

            // Search in admin table
            $sqlAdmin = "SELECT 'admin' AS type, id, nip AS identifier, password, nama, role, kontak, tanggal_lahir, tempat_lahir, foto_profil 
                        FROM admin 
                        WHERE nip = ?";
            $stmtAdmin = $this->db->prepareAndExecute($sqlAdmin, [$identifier]);
            $resultAdmin = $this->db->fetchAll($stmtAdmin);

            return $resultAdmin ? $resultAdmin[0] : null; // If found in admin
        } catch (Exception $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    public function authenticate($identifier, $password)
    {
        $user = $this->findUser($identifier);

        if (!$user) {
            return false;
        }

        // Verify password
        if ($this->verifyPassword($password, $user['password'])) {
            // Remove password from session for security
            unset($user['password']);
            
            // Store user info in session
            $_SESSION['user'] = $user;
            
            return true;
        }

        return false;
    }

    public function verifyPassword($inputPassword, $hashedPassword)
    {
        return password_verify($inputPassword, $hashedPassword);
    }

    public function __destruct()
    {
        $this->db->close();
    }
}