<?php

require_once 'Repository.php';

class UserRepository extends Repository {

    public function getUsers(): ?array {
        $query = $this->database->connect()->prepare(
            "
            SELECT * FROM users;
            "
        );

        $query->execute();
    
        $users = $query->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }

    public function getUserByEmail(string $email){
        $query = $this->database->connect()->prepare(
            "
            SELECT * FROM users WHERE email = :email
            "
        );

        $query->bindParam(':email', $email);
        $query->execute();

        $user = $query->fetch(PDO::FETCH_ASSOC);
        return $user;
    }

    public function createUser(string $email, string $hashedPassword, string $firstname, string $lastname, string $bio = ''){

        $query = $this->database->connect()->prepare(
            "
            INSERT INTO users (firstname, lastname, email, password, bio, enabled)
                VALUES (?,?,?,?,?,?);
            "
        );

        # TODO try catach lub insert with to handle errors with Creating user

        #$query->bindParam(':email', $email);
        $query->execute([
            $firstname,
            $lastname,
            $email,
            $hashedPassword,
            $bio,
            1
        ]);
    }

    public function registerBusinessWithUser(array $userData, array $businessData): bool {
        $db = $this->database->connect();
        try {
            $db->beginTransaction();

            // KROK 1: Tworzysz rekord w 'users'
            $stmtUser = $db->prepare('
                INSERT INTO users (email, password, firstname, lastname, enabled)
                VALUES (?, ?, ?, ?, ?) RETURNING id
            ');
            $stmtUser->execute([
                $userData['email'], $userData['password'], 
                $userData['firstname'], $userData['lastname'], 1
            ]);
            $userId = $stmtUser->fetch(PDO::FETCH_ASSOC)['id'];

            // KROK 2: Tworzysz rekord w 'businesses'
            $stmtBiz = $db->prepare('
                INSERT INTO businesses (name, nip, category, city, street, house_number, postal_code, phone, email, description)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id
            ');
            $stmtBiz->execute([
                $businessData['name'], $businessData['nip'], $businessData['category'],
                $businessData['city'], $businessData['street'], $businessData['house_number'],
                $businessData['postal_code'], $businessData['phone'], $businessData['email'],
                $businessData['description']
            ]);
            $businessId = $stmtBiz->fetch(PDO::FETCH_ASSOC)['id'];

            // KROK 3: Łączysz ich w Twojej tabeli 'user_business' ze screena
            $stmtLink = $db->prepare('
                INSERT INTO user_business (user_id, business_id, role)
                VALUES (?, ?, ?)
            ');
            $stmtLink->execute([$userId, $businessId, 'owner']);

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log($e->getMessage()); // Zapisz błąd do logów serwera
            return false;
        }
    }


}