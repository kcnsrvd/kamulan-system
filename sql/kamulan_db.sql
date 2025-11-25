-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2025 at 01:31 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `kamulan_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`id`, `name`, `address`, `contact`, `created_at`, `delivery_fee`) VALUES
(1, 'Kamulan - Rizal, Laguna', 'Rizal, Laguna', '0917-000-0001', '2025-10-21 16:18:59', 35.00),
(2, 'Kamulan - San Pablo (P. Zulueta)', 'P. Zulueta, San Pablo City', '0917-000-0002', '2025-10-21 16:18:59', 60.00),
(3, 'Kamulan - San Pablo (A. Mabini)', 'A. Mabini, San Pablo City', '0917-000-0003', '2025-10-21 16:18:59', 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `featured_category_images`
--

CREATE TABLE `featured_category_images` (
  `id` int(11) NOT NULL,
  `category` varchar(255) NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `featured_category_images`
--

INSERT INTO `featured_category_images` (`id`, `category`, `image`, `created_at`) VALUES
(1, 'Solo Menu', '1763303513_kamulan-cover.jpg', '2025-11-14 10:03:44'),
(2, 'Sharing', '1763114709_kamulan-cover.jpg', '2025-11-14 10:05:09'),
(3, 'Main Dish', '1763114719_kamulan-cover.jpg', '2025-11-14 10:05:19'),
(4, 'Sandwich', '1763114733_kamulan-cover.jpg', '2025-11-14 10:05:33'),
(5, 'Dessert', '1763114768_kamulan-cover.jpg', '2025-11-14 10:06:08'),
(6, 'Platters', '1763114789_kamulan-cover.jpg', '2025-11-14 10:06:29'),
(7, 'Add-ons', '1763114805_kamulan-cover.jpg', '2025-11-14 10:06:45'),
(8, 'Drinks', '1763114824_kamulan-cover.jpg', '2025-11-14 10:07:04');

-- --------------------------------------------------------

--
-- Table structure for table `home_carousel_images`
--

CREATE TABLE `home_carousel_images` (
  `id` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `home_carousel_images`
--

INSERT INTO `home_carousel_images` (`id`, `image`, `created_at`) VALUES
(1, '1763114439_kamulan1.jpg', '2025-11-14 10:00:39'),
(3, '1763114497_kamulan-cover.jpg', '2025-11-14 10:01:37'),
(4, '1763114564_quesadilla.jpg', '2025-11-14 10:02:44'),
(6, '1763114588_kamulan-drinks.jpg', '2025-11-14 10:03:08');

-- --------------------------------------------------------

--
-- Table structure for table `menu_items`
--

CREATE TABLE `menu_items` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `flavor` varchar(100) DEFAULT NULL,
  `available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `menu_items`
--

INSERT INTO `menu_items` (`id`, `name`, `description`, `price`, `image`, `category`, `flavor`, `available`, `created_at`) VALUES
(5, 'Chicken Fingers with Fries - Cheddar Cheese', 'Crispy chicken fingers with cheddar cheese flavor and fries.', 114.00, 'chicken_fingers.jpg', 'Solo Menu', NULL, 1, '2025-10-23 13:50:37'),
(6, 'Chicken Fingers with Fries - Maple Sriracha', 'Crispy chicken fingers with maple sriracha glaze and fries.', 124.00, 'chicken_fingers.jpg', 'Solo Menu', NULL, 1, '2025-10-23 13:50:37'),
(7, 'Chicken Fingers with Fries - Smoked Barbecue', 'Smoky barbecue-flavored chicken fingers with fries.', 124.00, 'chicken_fingers.jpg', 'Solo Menu', NULL, 1, '2025-10-23 13:50:37'),
(8, 'Chicken Fingers with Fries - Garlic Glaze', 'Crispy garlic-glazed chicken fingers with fries.', 124.00, 'chicken_fingers.jpg', 'Solo Menu', NULL, 1, '2025-10-23 13:50:37'),
(9, 'Mozzaballs with Fries', 'Fried mashed potato balls stuffed with mozzarella cheese.', 134.00, 'mozzaballs_fries.jpg', 'Solo Menu', NULL, 1, '2025-10-23 13:50:37'),
(10, '4 Cheese Quesadilla with Fries', 'Large tortilla stuffed with four kinds of cheese and Mexican sauce.', 129.00, '4cheese_quesadilla_fries.jpg', 'Solo Menu', NULL, 1, '2025-10-23 13:50:37'),
(11, 'Lasagna with Fries or Garlic Bread', 'Classic baked lasagna served with fries or garlic bread.', 184.00, 'lasagna_fries_garlicbread.jpg', 'Solo Menu', NULL, 1, '2025-10-23 13:50:37'),
(12, 'Rib Floss Quesadilla with Fries', 'Big tortilla stuffed with cheese and rib floss served with fries.', 169.00, 'ribfloss_quesadilla_fries.jpg', 'Solo Menu', NULL, 1, '2025-10-23 13:50:37'),
(13, 'Cheese Cottage Pie', 'Mashed potato topped with cheese, corn, and carrots.', 179.00, 'cheese_cottage_pie.jpg', 'Solo Menu', NULL, 1, '2025-10-23 13:50:37'),
(14, 'Nachos', 'Good for 2–3. Crunchy nachos with toppings.', 169.00, 'nachos.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(15, '4 Cheese Quesadillas', 'Good for 2–3. Cheesy quesadillas with Mexican sauce.', 219.00, '4cheese_quesadillas.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(16, 'Chicken Quesadillas', 'Good for 2–3. Cheesy quesadillas with fried chicken filling.', 219.00, 'chicken_quesadillas.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(17, 'Rib Floss Quesadillas', 'Good for 2–3. Quesadilla stuffed with rib floss.', 254.00, 'ribfloss_quesadillas.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(18, 'Chicken Fingers - Cheddar Cheese', 'Crispy chicken fingers, cheddar cheese flavor.', 184.00, '1763291312_chicken_fingers_sharing.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(19, 'Chicken Fingers - Maple Sriracha', 'Crispy chicken fingers with maple sriracha glaze.', 204.00, '1763291337_chicken_fingers_sharing.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(20, 'Chicken Fingers - Smoked Barbecue', 'Smoky chicken fingers.', 204.00, '1763291355_chicken_fingers_sharing.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(21, 'Chicken Fingers - Garlic Glaze', 'Crispy garlic glazed chicken fingers.', 204.00, '1763303559_chicken_fingers_sharing.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(22, 'Overload Fries - Cheesy Overload', 'Loaded fries with melted cheese.', 99.00, 'overload_fries.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(23, 'Overload Fries - Cheesy Barbecue', 'Loaded fries with barbecue and cheese.', 149.00, 'overload_fries.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(24, 'Overload Fries - Cheesy Mushroom', 'Loaded fries with cheese and mushrooms.', 149.00, 'overload_fries.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(25, 'Overload Fries - Taco Beef', 'Loaded fries with taco beef and cheese.', 149.00, 'overload_fries.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(26, 'Mozzaballs (7 pcs)', '7 pieces of cheesy potato balls.', 165.00, 'mozzaballs.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(27, 'Mozzaballs (10 pcs)', '10 pieces of cheesy potato balls.', 219.00, 'mozzaballs.jpg', 'Sharing', NULL, 1, '2025-10-23 13:50:37'),
(28, 'Chicken Fingers with Rice - Cheddar Cheese', 'With rice and coleslaw, cheddar cheese flavor.', 149.00, '1763306341_md chicken fingers w coleslaw.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(29, 'Chicken Fingers with Rice - Maple Sriracha', 'With rice and coleslaw, maple sriracha glaze.', 155.00, '1763306356_md chicken fingers w coleslaw.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(30, 'Chicken Fingers with Rice - Smoked Barbecue', 'With rice and coleslaw, smoked barbecue flavor.', 155.00, '1763306366_md chicken fingers w coleslaw.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(31, 'Chicken Fingers with Rice - Garlic Glaze', 'With rice and coleslaw, garlic glaze flavor.', 155.00, '1763306383_md chicken fingers w coleslaw.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(32, 'Classic Chicken Fingers', 'Served with rice, egg, and gravy.', 155.00, '1763306400_classic chicken fingers.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(33, 'Fried Chicken Thigh Fillet', 'Deep-fried chicken thigh fillet with rice and gravy.', 194.00, '1763306415_fried chicken thigh fillet.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(34, 'Country Fried Porkchop', 'Deep-fried pork chop with rice and gravy.', 199.00, '1763306434_country fried porkchop.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(35, 'Chicken Katsu', 'Breaded chicken fillet with sesame rice and katsu salad.', 199.00, '1763306459_chicken katsu.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(36, 'Tonkatsu (Pork)', 'Breaded pork cutlet with sesame rice and katsu salad.', 249.00, '1763306474_tonkatsu (pork).jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(37, 'Prawn Cutlet', '4 pieces of prawn with sesame rice and katsu salad.', 249.00, '1763306489_prawn cutlet.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(38, 'Will Combo Meal', 'Combo: Mozzaballs, Turones/Fries, Chicken Fingers with Rice.', 219.00, '1763306503_will combo meal.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(39, 'Burger Steak', 'Burger patty with rice, mushroom gravy, and veggies.', 219.00, '1763306521_burger steak.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(40, 'Seared Pepper Pork Steak', 'Served with java rice, veggies, and mushroom gravy.', 219.00, '1763306537_seared pepper pork steak.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(41, 'Seared Pork Belly', 'Served with java rice, veggies, and mushroom gravy.', 219.00, '1763306551_seared pork belly.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(42, 'Baby Back Ribs', 'Slow-cooked pork ribs with java rice or fries.', 319.00, '1763306578_baby back ribs.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(43, 'Burger Steak on Cottage Pie', 'Burger steak topped with cottage pie.', 359.00, '1763306602_burger steak on cottage pie.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(44, 'Baby Back Ribs with Mozzaballs', 'Ribs paired with cheesy mozzaballs.', 349.00, '1763306618_baby back ribs with mozzaballs.jpg', 'Main Dish', NULL, 1, '2025-10-23 13:50:37'),
(45, 'Chicken Burger with Fries', 'Deep-fried chicken fillet with lettuce, tomato, and ranch sauce.', 179.00, 'assets/images/chicken_burger.jpg', 'Sandwich', NULL, 1, '2025-10-23 13:51:46'),
(46, '4 Cheese Burger with Fries', 'Four kinds of cheese with lettuce, tomato, and ranch sauce.', 199.00, 'assets/images/cheese_burger.jpg', 'Sandwich', NULL, 1, '2025-10-23 13:51:46'),
(47, 'Mushroom Burger with Fries', 'Sautéed mushroom, melted cheese, cheddar sauce, lettuce, tomato, and ranch.', 199.00, 'assets/images/mushroom_burger.jpg', 'Sandwich', NULL, 1, '2025-10-23 13:51:46'),
(48, 'Taco Dog with Fries', 'Giant hotdog with taco and cheddar sauce.', 169.00, 'assets/images/taco_dog.jpg', 'Sandwich', NULL, 1, '2025-10-23 13:51:46'),
(49, 'Mushroom Dog with Fries', 'Giant hotdog with sautéed mushroom and cheddar sauce.', 169.00, 'assets/images/mushroom_dog.jpg', 'Sandwich', NULL, 1, '2025-10-23 13:51:46'),
(50, 'Cheese Roll Dog with Fries', 'Giant hotdog wrapped with four cheese and Mexican sauce.', 179.00, 'assets/images/cheese_dog.jpg', 'Sandwich', NULL, 1, '2025-10-23 13:51:46'),
(51, 'Quesadilla Burger with Fries', '140g burger patty wrapped in tortilla with tomato, lettuce, and mozzarella.', 254.00, 'assets/images/quesadilla_burger.jpg', 'Sandwich', NULL, 1, '2025-10-23 13:51:46'),
(52, 'Rib Floss Burger with Truffle Mayo', 'Savory rib floss burger with truffle mayo.', 209.00, 'assets/images/rib_burger.jpg', 'Sandwich', NULL, 1, '2025-10-23 13:51:46'),
(53, 'Bacon Barbecue Burger', 'Juicy burger topped with bacon and barbecue sauce.', 209.00, 'assets/images/bacon_burger.jpg', 'Sandwich', NULL, 1, '2025-10-23 13:51:46'),
(54, 'Taco Burger with Fries', 'Burger with taco sauce and fries on the side.', 209.00, 'assets/images/taco_burger.jpg', 'Sandwich', NULL, 1, '2025-10-23 13:51:46'),
(55, 'Turon de Keso', '10 pcs. turon with milk syrup and cheese.', 84.00, '1763940168_Copy of Green and Dark Green Authentic Filipino Food Menu (1).jpg', 'Dessert', NULL, 1, '2025-10-23 13:51:46'),
(56, 'Turon de Choco', '10 pcs. turon with chocolate sauce.', 84.00, '1763940181_Copy of Green and Dark Green Authentic Filipino Food Menu (1).jpg', 'Dessert', NULL, 1, '2025-10-23 13:51:46'),
(57, 'Churros', '150g churros with chocolate sauce.', 89.00, '1763940195_Copy of Green and Dark Green Authentic Filipino Food Menu (2).jpg', 'Dessert', NULL, 1, '2025-10-23 13:51:46'),
(58, 'Chocolate Ice Cream', '3 scoops with chocolate syrup and chocolate chips.', 89.00, '1763940247_Copy of Green and Dark Green Authentic Filipino Food Menu (3).jpg', 'Dessert', NULL, 1, '2025-10-23 13:51:46'),
(59, 'Strawberry Ice Cream', '3 scoops with strawberry syrup, grahams, and mallows.', 99.00, '1763940298_Copy of Green and Dark Green Authentic Filipino Food Menu (4).jpg', 'Dessert', NULL, 1, '2025-10-23 13:51:46'),
(60, 'Nachos Platter', 'Corn flakes with taco beef sauce, tomato, lettuce, and jalapeño pickles.', 449.00, 'assets/images/nachos_platter.jpg', 'Platters', NULL, 1, '2025-10-23 13:51:46'),
(61, '4 Cheese Quesadillas Platter', '4 big tortillas stuffed with cheese and Mexican sauce.', 469.00, 'assets/images/quesadilla_platter.jpg', 'Platters', NULL, 1, '2025-10-23 13:51:46'),
(62, 'Mozzaballs Platter', 'Large serving of cheesy potato balls.', 549.00, 'assets/images/mozzaballs_platter.jpg', 'Platters', NULL, 1, '2025-10-23 13:51:46'),
(63, 'Chicken Quesadilla Platter', '4 big tortillas with fried chicken and Mexican sauce.', 789.00, 'assets/images/chicken_quesadilla_platter.jpg', 'Platters', NULL, 1, '2025-10-23 13:51:46'),
(64, 'Chicken Fingers Platter', 'Chicken strips with cheddar, maple sriracha, and garlic glaze.', 549.00, 'assets/images/chicken_fingers_platter.jpg', 'Platters', NULL, 1, '2025-10-23 13:51:46'),
(65, 'Chicken Katsu Platter', 'Breaded chicken fillet with salad and sauce.', 599.00, 'assets/images/chicken_katsu_platter.jpg', 'Platters', NULL, 1, '2025-10-23 13:51:46'),
(66, 'Tonkatsu (Pork) Platter', 'Pork cutlet with salad and sauce.', 659.00, 'assets/images/tonkatsu_platter.jpg', 'Platters', NULL, 1, '2025-10-23 13:51:46'),
(67, 'Baby Back Ribs Platter', 'Slow-cooked pork ribs with BBQ sauce, onion, and sausage.', 1199.00, 'assets/images/ribs_platter.jpg', 'Platters', NULL, 1, '2025-10-23 13:51:46'),
(68, 'Lasagna Half Tray (6-8 pax)', 'Half tray serving of classic lasagna.', 1109.00, 'assets/images/lasagna_half.jpg', 'Platters', NULL, 1, '2025-10-23 13:51:46'),
(69, 'Lasagna Full Tray (12-15 pax)', 'Full tray serving of lasagna.', 1899.00, 'assets/images/lasagna_full.jpg', 'Platters', NULL, 1, '2025-10-23 13:51:46'),
(70, 'Plain Rice', 'Single serving of plain rice.', 28.00, 'assets/images/plain_rice.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(71, 'Java Rice', 'Flavored rice with garlic and spices.', 48.00, 'assets/images/java_rice.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(72, 'Sesame Rice', 'Rice cooked with sesame flavor.', 38.00, 'assets/images/sesame_rice.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(73, 'Mozzaballs Add-on', 'Extra piece of mozzarella ball.', 24.00, 'assets/images/mozzaballs.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(74, 'Garlic Bread', 'Single piece of garlic bread.', 15.00, 'assets/images/garlic_bread.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(75, 'Small Sauce', 'Small serving of sauce.', 18.00, 'assets/images/sauce_small.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(76, 'Big Sauce', 'Large serving of sauce.', 43.00, 'assets/images/sauce_big.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(77, 'Solo Fries', 'Single serving of fries.', 49.00, 'assets/images/fries.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(78, 'Katsu Salad', 'Side serving of katsu salad.', 33.00, 'assets/images/katsu_salad.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(79, 'Mixed Veggies', 'Side serving of mixed vegetables.', 33.00, 'assets/images/mixed_veggies.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(80, 'Mushroom Gravy', 'Single serving of mushroom gravy.', 28.00, 'assets/images/mushroom_gravy.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(81, 'Gravy', 'Classic Kamulan gravy.', 23.00, 'assets/images/gravy.jpg', 'Add-ons', NULL, 1, '2025-10-23 13:51:46'),
(82, 'Aloe Vera Pitcher', 'Refreshing aloe vera drink (pitcher).', 194.00, 'assets/images/aloe_vera_drink.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(83, 'Pomegranate Pitcher', 'Fruity pomegranate drink (pitcher).', 194.00, 'assets/images/pomegranate_drink.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(84, 'Mixed Berries & Grapes Pitcher', 'Pitcher of berry-grape goodness.', 194.00, 'assets/images/berries_grapes.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(85, 'Mango Pitcher', 'Sweet mango drink (pitcher).', 144.00, 'assets/images/mango_drink.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(86, 'Four Season Pitcher', 'Refreshing four-season fruit blend.', 134.00, 'assets/images/four_season.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(87, 'Pineapple Pitcher', 'Fresh pineapple juice.', 134.00, 'assets/images/pineapple.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(88, 'Coke 1.5L', '1.5-liter Coca-Cola.', 80.00, 'assets/images/coke15.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(89, 'Coke 1.5L Zero Sugar', '1.5-liter Coke Zero.', 100.00, 'assets/images/cokezero15.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(90, 'Affogato', 'Espresso with vanilla ice cream.', 99.00, 'assets/images/affogato.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(91, 'Iced Coffee with Vanilla Ice Cream', 'Iced coffee with vanilla ice cream topping.', 139.00, 'assets/images/iced_coffee_vanilla.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(92, 'Iced Coffee (No Ice Cream)', 'Plain iced coffee.', 129.00, 'assets/images/iced_coffee.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(93, 'Cold Chocolate', 'Cold chocolate drink.', 55.00, 'assets/images/cold_choco.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(94, 'Hot Chocolate', 'Hot choco with milk.', 55.00, 'assets/images/hot_choco.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(95, 'Hot Latte', 'Classic hot latte.', 100.00, 'assets/images/latte.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(96, 'Black Americano', 'Strong brewed black coffee.', 55.00, 'assets/images/americano.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(97, 'Four Season (Solo)', 'Single serving of four season drink.', 59.00, 'assets/images/four_season_solo.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(98, 'Pineapple (Solo)', 'Single serving pineapple juice.', 59.00, 'assets/images/pineapple_solo.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(99, 'Fresh Lemon', 'Freshly squeezed lemon juice.', 54.00, 'assets/images/lemon.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(100, 'Coke (Can)', 'Canned Coca-Cola.', 24.00, 'assets/images/coke_can.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(101, 'Sprite (Can)', 'Canned Sprite.', 24.00, 'assets/images/sprite_can.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(102, 'Royal (Can)', 'Canned Royal.', 24.00, 'assets/images/royal_can.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(103, 'Wilkins Water', 'Bottled Wilkins drinking water.', 22.00, 'assets/images/wilkins.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(104, 'Red Horse Beer', 'Beer in can.', 79.00, 'assets/images/redhorse.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(105, 'San Mig Light', 'Light beer in can.', 79.00, 'assets/images/sanmiglight.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(106, 'San Mig Pale Pilsen', 'Classic Pale Pilsen beer.', 79.00, 'assets/images/sanmigpale.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(107, 'San Mig Apple', 'Apple-flavored beer.', 79.00, 'assets/images/sanmigapple.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(108, 'Mango Shake Graham', 'Creamy mango shake with graham.', 170.00, 'assets/images/mango_shake.jpg', 'Drinks', NULL, 1, '2025-10-23 13:51:46'),
(109, 'Chicken Fingers with Fries - Salted Egg', 'Crispy chicken fingers with salted egg flavor and fries.', 150.00, 'chicken_fingers.jpg', 'Solo Menu', NULL, 1, '2025-11-02 11:55:39');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `branch_id` int(11) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `note` text DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL,
  `payment_method` enum('COD','GCash') DEFAULT 'COD',
  `status` enum('Placed','Preparing','Out for Delivery','Delivered','Cancelled') DEFAULT 'Placed',
  `rider_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `delivered_at` datetime DEFAULT NULL,
  `delivery_fee` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `branch_id`, `address`, `phone`, `note`, `total`, `payment_method`, `status`, `rider_id`, `created_at`, `delivered_at`, `delivery_fee`) VALUES
(1, 4, 1, 'Rizal, Laguna', NULL, NULL, 114.00, 'COD', 'Delivered', NULL, '2025-10-22 14:11:04', NULL, 35.00),
(2, 4, 1, NULL, NULL, NULL, 268.00, 'COD', 'Delivered', NULL, '2025-10-23 15:37:03', NULL, 35.00),
(3, 4, 1, 'rizal, laguna', NULL, NULL, 682.00, 'COD', 'Delivered', NULL, '2025-10-26 14:51:55', NULL, 35.00),
(4, 4, 1, 'House 001, Purok 1, Near Pook Brgy. Hall', NULL, NULL, 745.00, 'COD', 'Delivered', 2, '2025-10-28 14:50:49', NULL, 35.00),
(5, 7, 2, 'House 0002, Purok 2, Near Pook Elem. School', NULL, NULL, 602.00, 'COD', 'Delivered', 9, '2025-10-29 08:23:48', '2025-10-29 17:35:22', 60.00),
(6, 7, 2, 'House 0002, Purok 2, Near Pook Elem. School', NULL, NULL, 877.00, 'COD', 'Delivered', 9, '2025-10-29 09:36:54', '2025-10-29 17:46:17', 60.00),
(7, 4, 2, 'House 001, Purok 1, Near Pook Brgy. Hall', NULL, NULL, 660.00, 'COD', 'Delivered', 9, '2025-10-29 09:53:49', '2025-10-29 17:55:12', 60.00),
(8, 7, 2, 'House 0002, Purok 2, Near Pook Elem. School', NULL, NULL, 149.00, 'COD', 'Delivered', 9, '2025-10-31 05:26:02', '2025-10-31 13:28:57', 60.00),
(9, 7, 2, 'Purok 5 Near Brgy. hall, Brgy. Pook, Rizal, Laguna', NULL, NULL, 258.00, 'COD', 'Delivered', 9, '2025-11-02 08:05:23', '2025-11-02 16:44:04', 60.00),
(10, 7, 1, 'Purok 5 Near Brgy. Hall Brgy. Pook, Rizal, Laguna', '09123456788', NULL, 224.00, 'COD', 'Delivered', 2, '2025-11-02 08:39:57', '2025-11-02 21:53:55', 35.00),
(11, 7, 2, 'House 0002, Purok 2, Near Pook Elem. School, Brgy. Pook, Rizal, Laguna', '09123456789', NULL, 373.00, 'COD', 'Delivered', 9, '2025-11-02 14:29:01', '2025-11-02 22:45:25', 60.00),
(12, 7, 2, 'House 0002, Purok 2, Near Pook Elem. School, Brgy. Pook, Rizal, Laguna', '09123456789', NULL, 174.00, 'COD', 'Delivered', 9, '2025-11-02 23:34:34', '2025-11-03 07:38:17', 60.00),
(13, 7, 2, 'House 0002, Purok 2, Near Pook Elem. School, Brgy. Pook, Rizal, Laguna', '09123456789', NULL, 288.00, 'COD', 'Delivered', 9, '2025-11-03 00:07:35', '2025-11-03 08:09:04', 60.00),
(14, 7, 2, 'House 0002, Purok 2, Near Pook Elem. School, Brgy. Pook, Rizal, Laguna', '09123456789', NULL, 239.00, 'COD', 'Delivered', 9, '2025-11-14 11:38:50', '2025-11-16 19:29:47', 60.00),
(15, 7, 2, ',,,,,', '09123456788', NULL, 189.00, 'COD', 'Cancelled', 9, '2025-11-14 13:31:55', NULL, 60.00),
(16, 7, 2, 'Purok 5, Near Flying V Gas Station, Brgy. Pook, Rizal, Laguna', '09123456889', NULL, 264.00, 'COD', 'Delivered', 9, '2025-11-16 11:24:29', '2025-11-25 19:25:58', 60.00),
(17, 7, 2, 'Purok 1, Near Antipolo Elementary School, Brgy. Antipolo, Rizal, Laguna', '09123456889', NULL, 338.00, 'COD', 'Delivered', 9, '2025-11-24 00:13:39', '2025-11-24 08:30:13', 60.00),
(18, 7, 2, 'Purok 1, Near Antipolo Elementary School, Brgy. Antipolo, Rizal, Laguna', '09123456889', '', 338.00, 'COD', 'Cancelled', NULL, '2025-11-24 00:13:39', NULL, 60.00),
(19, 7, 2, 'Purok 1, Near Antipolo Elementary School, Brgy. Antipolo, Rizal, Laguna', '09123456889', NULL, 328.00, 'COD', 'Delivered', 9, '2025-11-24 00:27:50', '2025-11-25 19:25:56', 60.00),
(20, 7, 2, 'Purok 1, Near Antipolo Elementary School, Brgy. Antipolo, Rizal, Laguna', '09123456889', '', 328.00, 'COD', 'Cancelled', NULL, '2025-11-24 00:27:50', NULL, 60.00),
(21, 7, 2, 'Purok 1, Near Antipolo Elementary School, Brgy. Antipolo, Rizal, Laguna', '09123456889', NULL, 462.00, 'COD', 'Delivered', 9, '2025-11-25 11:22:23', '2025-11-25 19:25:31', 60.00),
(22, 11, 1, 'Purok 5, Near Pook Elementary School, Brgy. Pook, Rizal, Laguna', '09610278487', NULL, 178.00, 'COD', 'Delivered', 2, '2025-11-25 12:08:23', '2025-11-25 20:11:53', 35.00),
(23, 7, 1, 'Purok 1, Near Antipolo Elementary School, Brgy. Antipolo, Rizal, Laguna', '09123456889', NULL, 322.00, 'COD', 'Delivered', 2, '2025-11-25 12:14:36', '2025-11-25 20:16:18', 35.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `menu_item_id` int(11) DEFAULT NULL,
  `qty` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `addons` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `menu_item_id`, `qty`, `price`, `addons`) VALUES
(1, 1, 2, 1, 84.00, NULL),
(2, 2, 5, 1, 114.00, NULL),
(3, 2, 10, 1, 129.00, NULL),
(4, 3, 27, 3, 219.00, NULL),
(5, 4, 10, 2, 129.00, NULL),
(6, 4, 12, 2, 169.00, NULL),
(7, 4, 8, 1, 124.00, NULL),
(8, 5, 45, 1, 179.00, NULL),
(9, 5, 39, 1, 219.00, NULL),
(10, 5, 88, 1, 80.00, NULL),
(11, 5, 57, 1, 89.00, NULL),
(12, 6, 62, 1, 549.00, NULL),
(13, 6, 83, 1, 194.00, NULL),
(14, 6, 59, 1, 99.00, NULL),
(15, 7, 55, 2, 84.00, NULL),
(16, 7, 85, 1, 144.00, NULL),
(17, 7, 5, 1, 114.00, NULL),
(18, 7, 47, 1, 199.00, NULL),
(19, 8, 5, 1, 114.00, NULL),
(20, 9, 5, 1, 114.00, NULL),
(21, 9, 56, 1, 84.00, NULL),
(22, 10, 34, 1, 199.00, NULL),
(23, 11, 9, 1, 134.00, NULL),
(24, 11, 13, 1, 179.00, NULL),
(25, 12, 5, 1, 114.00, NULL),
(26, 13, 5, 2, 114.00, NULL),
(27, 14, 13, 1, 179.00, NULL),
(28, 15, 10, 1, 129.00, NULL),
(29, 16, 20, 1, 204.00, NULL),
(30, 17, 13, 1, 179.00, NULL),
(31, 17, 22, 1, 99.00, NULL),
(32, 19, 9, 2, 134.00, NULL),
(33, 21, 9, 1, 134.00, NULL),
(34, 21, 16, 1, 219.00, NULL),
(35, 21, 77, 1, 49.00, NULL),
(36, 22, 10, 1, 129.00, NULL),
(37, 22, 77, 1, 49.00, NULL),
(38, 23, 17, 1, 254.00, NULL),
(39, 23, 78, 1, 33.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(120) DEFAULT NULL,
  `last_name` varchar(120) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('buyer','staff','rider','manager') DEFAULT 'buyer',
  `branch_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `street` varchar(150) DEFAULT NULL,
  `barangay` varchar(100) DEFAULT NULL,
  `municipality` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `house_number` varchar(50) DEFAULT NULL,
  `nearest_landmark` varchar(150) DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `password`, `role`, `branch_id`, `created_at`, `street`, `barangay`, `municipality`, `province`, `house_number`, `nearest_landmark`, `is_available`) VALUES
(1, 'Manager', 'Kamulan', 'manager@kamulan.com', '09170000000', '$2y$10$dQdP2RQC02ox05NxUeEEM.8U48797kKnWmmCuZkXsEXoFr4sWaM6i', 'manager', NULL, '2025-10-21 16:18:59', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(2, 'Juan', 'Rizal', 'juan_rizal@rider.com', '09170000001', '$2y$10$dQdP2RQC02ox05NxUeEEM.8U48797kKnWmmCuZkXsEXoFr4sWaM6i', 'rider', 1, '2025-10-21 16:18:59', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(3, 'Ana', 'Mabini', 'ana_mabini@staff.com', '09170000002', '$2y$10$dQdP2RQC02ox05NxUeEEM.8U48797kKnWmmCuZkXsEXoFr4sWaM6i', 'staff', 3, '2025-10-21 16:18:59', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(4, 'Customer', 'Cruz', 'customer@gmail.com', '09170000003', '$2y$10$dQdP2RQC02ox05NxUeEEM.8U48797kKnWmmCuZkXsEXoFr4sWaM6i', 'buyer', NULL, '2025-10-21 16:18:59', 'Purok 1', 'Pook', 'Rizal', 'Laguna', '001', 'Pook Brgy. Hall', 1),
(5, 'Jane', 'Rizal', 'jane_rizal@staff.com', '09123456789', '$2y$10$dQdP2RQC02ox05NxUeEEM.8U48797kKnWmmCuZkXsEXoFr4sWaM6i', 'staff', 1, '2025-10-23 15:39:33', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(6, 'Eljoysa', 'Tajao', 'eljoysa@gmail.com', '09456123456', '$2y$10$a7Lw4k9dk3VstkjyCzK76.my/rh7MpupFulxivDII7CbHtgYnxdRm', 'buyer', NULL, '2025-10-25 09:13:28', 'Purok 1', 'Tala', 'Rizal', 'Laguna', NULL, NULL, 1),
(7, 'Kyla', 'Conservado', 'kylaconservado558@gmail.com', '09123456889', '$2y$10$pWE6AH.9GJwTt9DlQ8SVK.0BQMuLmRLkUaW/gR6UjDUQyQjqvTTU6', 'buyer', NULL, '2025-10-29 08:05:21', 'Purok 1', 'Antipolo', 'Rizal', 'Laguna', '', 'Antipolo Elementary School', 1),
(8, 'Mark', 'Mabini', 'mark_mabini@rider.com', '09987654321', '$2y$10$/.5wDhkN5bBRwPl2dA82yuiv6brCxbexF9iZTTmcnW0jPFXcpsdeK', 'rider', 3, '2025-10-29 08:12:03', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(9, 'Sean', 'Zulueta', 'sean_zulueta@rider.com', '09456123456', '$2y$10$kWzwce/pcyORgy3SUJ0Y7OszANt.Ir3ozOfHWm0hTAeEzGotDsl0y', 'rider', 2, '2025-10-29 08:14:07', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(10, 'Liza', 'Zulueta', 'liza_zulueta@staff.com', '09123456789', '$2y$10$uYMn3aFREV/GB1r3wLVG3u0lce//BoLAfbgWoThSyfkBfunMdj7RK', 'staff', 2, '2025-10-29 08:15:03', NULL, NULL, NULL, NULL, NULL, NULL, 1),
(11, 'julia dale', 'dela torre', 'juliadelatorre523@gmail.com', '09610278487', '$2y$10$/5VPxhaBU46G3pnVLbYoXOGH02ATWtjVgoZYcCfT6RytnSLti3p3S', 'buyer', NULL, '2025-10-31 05:18:52', 'Purok 5', 'Pook', 'Rizal', 'Laguna', '', 'Pook Elementary School', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `featured_category_images`
--
ALTER TABLE `featured_category_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `home_carousel_images`
--
ALTER TABLE `home_carousel_images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `menu_items`
--
ALTER TABLE `menu_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `featured_category_images`
--
ALTER TABLE `featured_category_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `home_carousel_images`
--
ALTER TABLE `home_carousel_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `menu_items`
--
ALTER TABLE `menu_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
