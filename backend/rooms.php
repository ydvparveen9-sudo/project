<?php
/*
FILE OVERVIEW:
- backend\rooms.php
- Backend logic file: form data process karta hai, validation karta hai, aur JSON/session storage se interact karta hai.
- Is file me comments ka maksad beginner ko code flow samjhana hai bina logic badle.
*/


// Storage helpers load: room assignment read/write functions include karta hai.
require_once __DIR__ . '/rooms_store.php';

// Initial data load: current room assignments memory me laata hai.
$rooms = load_rooms();



