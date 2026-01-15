<?php

require_once 'Repository.php';

class UserRepository extends Repository
{

    public function getUsers(): ?array
    {
        $query = $this->database->connect()->prepare(
            "
            SELECT * FROM users;
            "
        );

        $query->execute();

        $users = $query->fetchAll(PDO::FETCH_ASSOC);
        return $users;
    }

    public function getUserByEmail(string $email)
    {
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


    public function createUser(string $email, string $hashedPassword, string $firstname, string $lastname, string $imageUrl)
    {
        if (!$imageUrl) {
            $imageUrl = 'https://lh3.googleusercontent.com/aida-public/AB6AXuAkl7q-j7rxaTrAQ59t4u3j_K_rrRptrqlLEsYN2ECyKS6k9yoL0bxNiS2-EMHfIVgVvYfup_BAwDUcvRjkJKEaxmD_lpEzyc77lPzF-MoAIOu_Nbfa9MrIC3ly0bCfZod33WEE5KZj-Dx6de5toUnTYsSXap2Xkny4puh-ZNtiApyuOA8mqYFvf7UDNacc3EN4rz9MWhZyKLqHafS7yieU_dadYcT9Glod13ur6bCChVCzY8dK065gy1g4LB506F9MyuujYOsYTok';
        }

        $query = $this->database->connect()->prepare(
            "
            INSERT INTO users (firstname, lastname, email, password, image_url, enabled)
            VALUES (?, ?, ?, ?, ?, ?);
            "
        );

        $query->execute([
            $firstname,
            $lastname,
            $email,
            $hashedPassword,
            $imageUrl,
            1
        ]);
    }

    public function updateSettings(string $email, bool $emailNotif, bool $smsNotif)
    {
        $stmt = $this->database->connect()->prepare('
            UPDATE users 
            SET email_notifications = :emailNotif, sms_notifications = :smsNotif 
            WHERE email = :email
        ');

        $stmt->bindParam(':emailNotif', $emailNotif, PDO::PARAM_BOOL);
        $stmt->bindParam(':smsNotif', $smsNotif, PDO::PARAM_BOOL);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);

        return $stmt->execute();
    }

    public function registerBusinessWithUser(array $userData, array $businessData): bool
    {
        $db = $this->database->connect();
        try {
            $db->beginTransaction();

            // RECORD IN USERS
            $stmtUser = $db->prepare('
                INSERT INTO users (email, password, firstname, lastname, enabled)
                VALUES (?, ?, ?, ?, ?) RETURNING id
            ');
            $stmtUser->execute([
                $userData['email'],
                $userData['password'],
                $userData['firstname'],
                $userData['lastname'],
                1
            ]);
            $userId = $stmtUser->fetch(PDO::FETCH_ASSOC)['id'];

            // RECORD IN BUSSINESS
            $stmtBiz = $db->prepare('
                INSERT INTO businesses (name, nip, category, city, street, house_number, postal_code, phone, email, description, rating_avg, image_url)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id
            ');
            $stmtBiz->execute([
                $businessData['name'],
                $businessData['nip'],
                $businessData['category'],
                $businessData['city'],
                $businessData['street'],
                $businessData['house_number'],
                $businessData['postal_code'],
                $businessData['phone'],
                $businessData['email'],
                $businessData['description'],
                0.00,
                $businessData['image_url']
            ]);
            $businessId = $stmtBiz->fetch(PDO::FETCH_ASSOC)['id'];

            // SCALING USER_BUSINESS
            $stmtLink = $db->prepare('
                INSERT INTO user_business (user_id, business_id, role)
                VALUES (?, ?, ?)
            ');
            $stmtLink->execute([$userId, $businessId, 'owner']);

            $db->commit();
            return true;
        } catch (PDOException $e) {
            $db->rollBack();
            error_log($e->getMessage());
            return false;
        }
    }


    public function updateUserDetails(int $id, string $firstname, string $lastname, string $email, ?string $password, ?string $bio, ?string $imageUrl): void
    {
        $queryBuilder = "UPDATE users SET firstname = :firstname, lastname = :lastname, email = :email, bio = :bio";

        $params = [
            ':id' => $id,
            ':firstname' => $firstname,
            ':lastname' => $lastname,
            ':email' => $email,
            ':bio' => $bio
        ];

        if ($password) {
            $queryBuilder .= ", password = :password";
            $params[':password'] = $password;
        }

        if ($imageUrl) {
            $queryBuilder .= ", image_url = :image_url";
            $params[':image_url'] = $imageUrl;
        }

        $queryBuilder .= " WHERE id = :id";

        $stmt = $this->database->connect()->prepare($queryBuilder);
        $stmt->execute($params);
    }
}