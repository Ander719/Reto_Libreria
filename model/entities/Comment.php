<?php
class Comment {
    private $profileCode;
    private $isbn;
    private $commentText;
    private $rating;
    private $dateComment;

    public function __construct($profileCode = null, $isbn = null, $commentText = null, $rating = null, $dateComment = null) {
        $this->profileCode = $profileCode;
        $this->isbn = $isbn;
        $this->commentText = $commentText;
        $this->rating = $rating;
        $this->dateComment = $dateComment;
    }

    // Getters
    public function getProfileCode() {
         return $this->profileCode; 
        }
    public function getIsbn() {
         return $this->isbn; 
        }
    public function getCommentText() {
         return $this->commentText; 
        }
    public function getRating() {
         return $this->rating; 
        }
    public function getDateComment() {
         return $this->dateComment; 
        }

    // Setters
    public function setProfileCode($profileCode) {
         $this->profileCode = $profileCode; 
        }
    public function setIsbn($isbn) {
         $this->isbn = $isbn; 
        }
    public function setCommentText($commentText) {
         $this->commentText = $commentText; 
        }
    public function setRating($rating) {
         $this->rating = $rating; 
        }
    public function setDateComment($dateComment) {
         $this->dateComment = $dateComment; 
        }
}
?>