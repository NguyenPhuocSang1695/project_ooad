<?php

class User {
    private $username;
    private $fullname;
    private $email;
    private $phone;
    private $role;
    private $status;
    private $address;

    public function __construct($data = []) {
        $this->username = $data['Username'] ?? '';
        $this->fullname = $data['FullName'] ?? '';  // Fixed case sensitivity
        $this->email = $data['Email'] ?? '';
        $this->phone = $data['Phone'] ?? '';
        $this->role = $data['Role'] ?? 'customer';
        $this->status = $data['Status'] ?? 'Active';
        $this->address = $data['Address'] ?? '';
    }

    // Getters
    public function getUsername() {
        return $this->username;
    }

    public function getFullname() {
        return $this->fullname;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPhone() {
        return $this->phone;
    }

    public function getRole() {
        return $this->role;
    }

    public function getStatus() {
        return $this->status;
    }

    public function getAddress() {
        return $this->address;
    }

    // Setters
    public function setUsername($username) {
        $this->username = $username;
    }

    public function setFullname($fullname) {
        $this->fullname = $fullname;
    }

    public function setEmail($email) {
        $this->email = $email;
    }

    public function setPhone($phone) {
        $this->phone = $phone;
    }

    public function setRole($role) {
        $this->role = $role;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setAddress($address) {
        $this->address = $address;
    }

    // Helper methods
    public function isAdmin() {
        return strtolower($this->role) === 'admin';
    }

    public function isActive() {
        return $this->status === 'Active';
    }

    // Convert user object to array
    public function toArray() {
        return [
            'Username' => $this->username,
            'Fullname' => $this->fullname,
            'Email' => $this->email,
            'Phone' => $this->phone,
            'Role' => $this->role,
            'Status' => $this->status,
            'Address' => $this->address
        ];
    }

    // Format status text for display
    public function getStatusText() {
        return $this->isActive() ? 'Hoạt động' : 'Đã khóa';
    }

    // Format role text for display
    public function getRoleText() {
        return $this->isAdmin() ? 'Quản trị viên' : 'Khách hàng';
    }
}