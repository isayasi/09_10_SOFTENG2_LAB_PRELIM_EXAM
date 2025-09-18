<?php  
require_once '../classloader.php';

if (isset($_POST['insertNewProposalBtn'])) {
    $user_id = $_SESSION['user_id'];
    $description = htmlspecialchars($_POST['description']);
    $min_price = htmlspecialchars($_POST['min_price']);
    $max_price = htmlspecialchars($_POST['max_price']);

    // Classification → subcategory_id
    $subcategory_id = $_POST['classification'];

    // ✅ Get category_id safely via Category class
    $categoryObj = new Category();
    $category_id = $categoryObj->getCategoryIdBySubcategory($subcategory_id);

    // File upload
    $fileName = $_FILES['image']['name'];
    $tempFileName = $_FILES['image']['tmp_name'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
    $uniqueID = sha1(md5(rand(1,9999999)));
    $imageName = $uniqueID.".".$fileExtension;
    $folder = "../../images/".$imageName;

    if (move_uploaded_file($tempFileName, $folder)) {
        if ($proposalObj->createProposal(
            $user_id, 
            $description, 
            $imageName, 
            $min_price, 
            $max_price, 
            $category_id, 
            $subcategory_id
        )) {
            $_SESSION['status'] = "200";
            $_SESSION['message'] = "Proposal saved successfully!";
            header("Location: ../index.php");
            exit();
        } else {
            $_SESSION['status'] = "400";
            $_SESSION['message'] = "Failed to save proposal!";
            header("Location: ../user.php");
            exit();
        }
    } else {
        $_SESSION['status'] = "400";
        $_SESSION['message'] = "Image upload failed!";
        header("Location: ../user.php");
        exit();
    }
}

// ✅ Keep your other blocks as-is
if (isset($_POST['loginUserBtn'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($email) && !empty($password)) {
        if ($userObj->loginUser($email, $password)) {
            header("Location: ../index.php");
        } else {
            $_SESSION['message'] = "Username/password invalid";
            $_SESSION['status'] = "400";
            header("Location: ../login.php");
        }
    } else {
        $_SESSION['message'] = "Please make sure there are no empty input fields";
        $_SESSION['status'] = '400';
        header("Location: ../login.php");
    }
}

if (isset($_GET['logoutUserBtn'])) {
    $userObj->logout();
    header("Location: ../index.php");
}

if (isset($_POST['updateUserBtn'])) {
    $contact_number = htmlspecialchars($_POST['contact_number']);
    $bio_description = htmlspecialchars($_POST['bio_description']);
    if ($userObj->updateUser($contact_number, $bio_description, $_SESSION['user_id'])) {
        $_SESSION['status'] = "200";
        $_SESSION['message'] = "Profile updated successfully!";
        header("Location: ../profile.php");
    }
}

if (isset($_POST['updateProposalBtn'])) {
    $min_price = $_POST['min_price'];
    $max_price = $_POST['max_price'];
    $proposal_id = $_POST['proposal_id'];
    $description = htmlspecialchars($_POST['description']);
    if ($proposalObj->updateProposal($description, $min_price, $max_price, $proposal_id)) {
        $_SESSION['status'] = "200";
        $_SESSION['message'] = "Proposal updated successfully!";
        header("Location: ../your_proposals.php");
    }
}

if (isset($_POST['deleteProposalBtn'])) {
    $proposal_id = $_POST['proposal_id'];
    $image = $_POST['image'];

    if ($proposalObj->deleteProposal($proposal_id)) {
        // Delete file inside images folder
        unlink("../../images/".$image);
        
        $_SESSION['status'] = "200";
        $_SESSION['message'] = "Proposal deleted successfully!";
        header("Location: ../your_proposals.php");
    }
}
