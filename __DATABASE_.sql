-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 21, 2024 at 12:34 AM
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
-- Database: `serverside`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `name`) VALUES
(1, 'Food'),
(2, 'Tokyo'),
(3, 'Anime'),
(4, 'Shopping'),
(8, 'Attractions'),
(9, 'Site News');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `comment_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `content`, `post_id`, `user_id`, `created_at`, `updated_at`) VALUES
(6, 'Thank you for the welcome!', 11, 4, '2024-12-19 04:15:37', '2024-12-19 04:15:37'),
(7, 'Hello', 11, 3, '2024-12-19 04:16:14', '2024-12-19 04:16:14'),
(8, 'This is cool', 19, 6, '2024-12-19 04:18:16', '2024-12-19 04:18:16'),
(10, 'I would want to climb this one day', 12, 3, '2024-12-20 17:25:54', '2024-12-20 17:25:54');

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `image_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `mime_type` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `images`
--

INSERT INTO `images` (`image_id`, `post_id`, `filename`, `original_filename`, `file_path`, `file_size`, `mime_type`, `created_at`) VALUES
(3, 4, 'post_image_6763df9468a6e.jpg', 'darwin-vegher-CPAajYWQeR4-unsplash.jpg', 'uploads/post_image_6763df9468a6e.jpg', 265827, 'image/jpeg', '2024-12-19 08:55:49'),
(4, 10, 'post_image_6763e3146ada9.jpg', 'manuel-cosentino-d92I1Of2ofQ-unsplash.jpg', 'uploads/post_image_6763e3146ada9.jpg', 211894, 'image/jpeg', '2024-12-19 09:10:45'),
(5, 1, 'post_image_6763e4282586c.jpg', 'jezael-melgoza-alY6_OpdwRQ-unsplash.jpg', 'uploads/post_image_6763e4282586c.jpg', 305345, 'image/jpeg', '2024-12-19 09:15:21'),
(6, 11, 'post_image_6763e81a56341.jpg', 'sora-sagano-8sOZJ8JF0S8-unsplash.jpg', 'uploads/post_image_6763e81a56341.jpg', 400223, 'image/jpeg', '2024-12-19 09:32:11'),
(7, 12, 'post_image_6763e9c94fb9d.jpg', 'marek-okon-4ECPE_Bbs-M-unsplash.jpg', 'uploads/post_image_6763e9c94fb9d.jpg', 54221, 'image/jpeg', '2024-12-19 09:39:21'),
(8, 15, 'post_image_6763ec8e11abc.jpg', 'jordan-duca-aOqEXM_zI_4-unsplash.jpg', 'uploads/post_image_6763ec8e11abc.jpg', 113324, 'image/jpeg', '2024-12-19 09:51:10'),
(9, 16, 'post_image_6763ed8cf09d9.jpg', 'nopparuj-lamaikul-_nwDUzEJDk0-unsplash.jpg', 'uploads/post_image_6763ed8cf09d9.jpg', 223521, 'image/jpeg', '2024-12-19 09:55:25'),
(10, 17, 'post_image_6763ee0331b2d.jpg', 'caroline-roose-mhJ2R_6MRh0-unsplash.jpg', 'uploads/post_image_6763ee0331b2d.jpg', 157286, 'image/jpeg', '2024-12-19 09:57:23'),
(11, 18, 'post_image_6763eeccdd72e.jpg', 'joan-tran-IuXtdvHNc2g-unsplash.jpg', 'uploads/post_image_6763eeccdd72e.jpg', 311015, 'image/jpeg', '2024-12-19 10:00:45'),
(12, 19, 'post_image_6763ef7694736.jpg', 'yu-kato-4ZZLUEzJAsI-unsplash.jpg', 'uploads/post_image_6763ef7694736.jpg', 259010, 'image/jpeg', '2024-12-19 10:03:35');

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE `pages` (
  `page_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_categories`
--

CREATE TABLE `page_categories` (
  `page_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `page_comments`
--

CREATE TABLE `page_comments` (
  `comment_id` int(11) NOT NULL,
  `page_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_visible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `post_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `author_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `slug` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` (`post_id`, `title`, `content`, `author_id`, `created_at`, `updated_at`, `slug`) VALUES
(1, 'Shibuya, the Heart of Tokyo', '<p>Shibuya hits you over the head with its sheer presence: the continuous\r\nflow of people, the glowing video screens and the tangible buzz. This\r\nis the beating heart of Tokyo’s youth culture, where the fashion is\r\nloud, the street culture vivid and the nightclubs run until dawn. It is a\r\nmust-see for anyone interested in Tokyo pop culture. Rumoured to be the busiest intersection\r\nin the world (and definitely in Japan),\r\nShibuya Crossing is like a giant beating\r\nheart, sending people in all directions with every pulsing light change.\r\nPerhaps nowhere else says ‘Welcome\r\nto Tokyo’ better than this. Hundreds\r\nof people – and at peak times said to\r\nbe over 1000 people – cross at a time,\r\ncoming from all directions at once yet\r\nstill managing to dodge each other with\r\na practised, nonchalant agility. (渋谷\r\nスクランブル交差点; Shibuya Scramble;\r\ndJR Yamanote line to Shibuya, Hachikō exit)</p>', 4, '2024-12-13 19:27:50', '2024-12-19 03:15:20', 'shibuya-the-heart-of-tokyo'),
(4, 'An Afternoon in Akihabara', '<p>Akihabara (Akiba to friends) is the centre of Tokyo’s otaku (geek) subculture. But you don’t have to obsess about manga (Japanese comics) or anime (Japanese animation) to enjoy this quirky neighbourhood. It’s equal parts sensory overload and cultural mind-bender. In fact, as the otaku subculture gains more and more influence on the culture at large, Akiba is drawing more visitors who don’t fit the stereotype.</p><p><br></p><p>Explore ‘Electric Town’</p><p>Before Akihabara became otaku-land, it was Electric Town – the place for discounted electronics and where early computer geeks tracked down obscure parts for home-built machines. Akihabara Radio Center (秋葉原ラジオセンター; 1-14-2 Soto-Kanda, Chiyoda-ku; hgenerally 10am-6pm; dJR Yamanote line to Akihabara, Electric Town exit), a warren of stalls under the train tracks, keeps the tradition alive.</p><p>2 Play Vintage Arcade Games</p><p>In Akihabara, a love of the new is tempered with a deep affection for the old. Super Potato Retro-kan (スーパー</p><p>ポテトレトロ館; www.superpotato.com; 1-11-2 Soto-kanda, Chiyoda-ku; h11am-8pm Mon-Fri, from 10am Sat &amp; Sun; dJR Yamanote line to Akihabara, Electric Town exit) is a retro video arcade with some old-school consoles.</p><p>3 Visit a Maid Cafe</p><p>Maid cafes – where waitresses dress as French maids and treat customers with giggling deference as go-shujinsama</p><p>(master) or o-jōsama (miss) – are an Akiba institution. Pop into @Home (@ほぉ～むカフェ; www.cafe-athome.com; 4th-7th fl, 1-11-4 Soto-Kanda, Chiyoda-ku; drinks from ¥500; h11.30am-10pm Mon-Fri, 10.30am-10pm Sat &amp; Sun; dJR Yamanote line to Akihabara, Electric Town exit) for a game of moe moe jankan (rock, paper, scissors) maid-style.</p><p>4 Shop at Mandarake Complex</p><p>To get an idea of what otaku obsess over, a trip to Mandarake Complex (まんだらけコンプレックス; www.mandarake.co.jp; 3-11-2 Soto-Kanda, Chiyoda-ku; hnoon-8pm; dJR Yamanote line to Akihabara, Electric Town exit) will do the trick. It’s eight storeys of comic books and DVDs, action figures and cel art.</p><p>5 Pop into Yodobashi Akiba</p><p>The modern avatar of Akihabara Radio Center is Yodobashi Akiba (ヨドバシカメラAkiba; www.yodobashi-akiba.com; 1-1 Kanda Hanaoka-chō, Chiyoda-ku; h9.30am-10pm; dJR Yamanote line to Akihabara, Shōwa-tōriguchi exit), a monster electronics store beloved by camera junkies. But for all the modern conveniences Yodobashi Akiba feels like an old-time bazaar.</p><p>6 Check out an Old Train Station</p><p>MAAch ecute (%03-3257-8910; www.maach-ecute.jp; 1-25-4 Kanda-Sudachō, Chiyoda-ku; h11am-9pm Mon-Sat, to 8pm Sun; dChūō or Sōbu lines to Akihabara, Electric Town exit) is a shopping and dining complex, crafted from the old station and railway arches of Mansei-bashi, selling homewares, fashion and foods from around Japan.</p><p>7 Visit a Trainspotters’ Cafe</p><p>While mAAch ecute mall may have little to do with otaku sensibilities, cafe N3331 (%03-5295-2788; http://n3331.com; 2nd fl, mAAch ecute, 1-25-4 Kanda-Sudachō, Chiyoda-ku; h11am-10.30pm Mon-Sat, to 8.30pm Sun; dJR Yamamote line to Akihabara, Electric Town exit), on the 2nd floor, will appeal to densha otaku (train geeks). From floor-to-ceiling windows, watch commuter trains stream by while sipping on coffee, craft beer or sake.</p>', 4, '2024-12-17 21:41:17', '2024-12-19 02:55:48', 'an-afternoon-in-akihabara'),
(10, 'My #1 Area, Shinjuku', '<p>Here in Shinjuku, much of what makes Tokyo tick is crammed into</p><p>one busy district: upscale department stores, anachronistic shanty</p><p>bars, buttoned-up government offices, swarming crowds, streetside</p><p>video screens, leafy parks, racy nightlife, hidden shrines and soaring </p><p>skyscrapers. It’s a fantastic introduction to Tokyo today, with all its</p><p>highs and lows.Shinjuku works neatly as a dayto-</p><p>night destination.</p>', 4, '2024-12-06 03:08:08', '2024-12-06 03:13:16', 'my-1-area-shinjuku'),
(11, 'Welcome Post', '<p>Welcome to the Rising Sun Travel Blog! All posts are excerpts from the two ebooks <a href=\"https://www.lonelyplanet.com/japan/tokyo\" target=\"_blank\">Lonely Planet Japan</a> and Lonely Planet Tokyo. All images are sourced royalty-free from <a href=\"unsplash.com\" target=\"_blank\">unsplash.com</a>. Happy reading!</p>', 4, '2024-12-20 03:25:07', '2024-12-19 04:10:14', 'welcome-post'),
(12, 'Mt Fuji, Japan\'s tallest mountain', '<p>Catching a glimpse of Mt Fuji (富士山; 3776m),</p><p>Japan’s highest and most famous peak, will take</p><p>your breath away. Climbing it and watching the sunrise</p><p>from the summit is one of Japan’s superlative</p><p>experiences (though it’s often cloudy). The official</p><p>climbing season runs from 1 July to 31 August. The</p><p>mountain is divided into 10 ‘stations’ from base</p><p>(First Station) to summit (20th). The vast majority</p><p>of visitors hike the Kawaguchi-ko Trail from the</p><p>Fifth Station, as it’s easy to reach from Tokyo.</p><p><br></p><p>The Climb</p><p>The Kawaguchi-ko Trail is accessed from Fuji</p><p>Subaru Line Fifth Station (aka Kawaguchi-ko</p><p>Fifth Station). Allow five to six hours to reach the</p><p>top (though some climb it in half the time) and</p><p>about three hours to descend, plus 1½ hours for</p><p>circling the crater at the top. To time your arrival</p><p>for dawn you can either start up in the afternoon,</p><p>stay overnight in a mountain hut and continue</p><p>early in the morning, or climb the whole way</p><p>at night. You do not want to arrive on the top</p><p>too long before dawn, as it will be very cold and</p><p>windy, even at the height of summer.</p><p>Know Before You Go</p><p>Mt Fuji is a serious mountain, high enough for</p><p>altitude sickness, and weather and visibility can</p><p>change instantly and dramatically. At a minimum,</p><p>bring clothing appropriate for cold and</p><p>wet weather, including a hat and gloves, at least</p><p>2L of water (you can buy more on the mountain</p><p>during the climbing season), snacks and cash for</p><p>other necessities, such as toilets (¥200). If you’re</p><p>climbing at night, bring a torch (flashlight) or</p><p>headlamp and spare batteries.</p><p>Fuji-spotting</p><p>Outside the climbing season, you can hunt for</p><p>views of Mt Fuji in the Fuji Five Lake region,</p><p>where placid lakes, formed by ancient eruptions,</p><p>serve as natural reflecting pools. Kawaguchi-ko</p><p>is the most popular lake, with plenty of accommodation,</p><p>eating and hiking options around it.</p><p>Winter and spring are your best bet for catching</p><p>a glimpse, though often the snow-capped peak</p><p>is visible only in the morning before it retreats</p><p>behind its cloud curtain. Buses run year-round to</p><p>Kawaguchi-ko (¥1750, 1¾ hours).</p>', 4, '2024-12-19 03:38:48', '2024-12-19 03:39:48', 'mt-fuji'),
(13, 'A Night Out in Shimo-Kitazawa', '<p>For 50 years, Shimokita (as it’s called here) has been a prism through which to see the city’s alternative side. While other neighbourhoods go big, Shimokita fiercely defends its small stature, its narrow, crooked roads (the bane of taxi drivers) and its analogue vibe. Spend an evening here and raise your glass to (and with) the characters committed to keeping Shimokita weird.</p>', 4, '2024-12-19 03:42:30', '2024-12-19 03:42:53', 'a-night-out-in-shimo-kitazawa'),
(15, 'Look at the picture I took!', '<p>Isn\'t it nice?</p>', 3, '2024-12-19 03:51:10', '2024-12-19 03:51:10', 'look-at-the-picture-i-took'),
(16, 'Asakusa', '<p>Asakusa (ah-saku-sah) is home to Tokyo’s oldest attraction, the</p><p>centuries-old</p><p>temple Sensō-ji. Just across the river is the city’s newest:</p><p>the 634m-tall Tokyo Sky Tree (pictured). The neighbourhoods surrounding</p><p>these sights are known as shitamachi (the low city), where the spirit</p><p>of old Edo (Tokyo under the shogun) proudly lives on in an atmospheric</p><p>web of alleys, artisan shops and mum-and-dad restaurants.</p>', 3, '2024-12-19 03:54:36', '2024-12-19 03:55:24', 'asakusa'),
(17, 'Tokyo Shopping', '<p>Where to Shop</p><p>Tokyo is famous for its fashion tribes, each of</p><p>whom has a preferred stomping ground. Ginza has</p><p>long been Tokyo’s premier shopping district and</p><p>has many high-end department stores and boutiques,</p><p>but also fast-fashion emporiums. Harajuku,</p><p>on the other side of town, has boutiques that deal</p><p>in both luxury fashion and street cred. Shibuya is</p><p>the locus of the teen-fashion trend machine.</p><p>For one-stop shopping, Shinjuku is your best</p><p>bet: here there are department stores, electronics</p><p>outfitters, book shops and more. Asakusa has</p><p>many stores selling artisan crafts, both traditional</p><p>and contemporary, which makes it a good place for</p><p>souvenir hunting.</p><p>Flea Markets</p><p>Flea markets and antique fairs pop up regularly</p><p>around Tokyo, with many taking place at shrines;</p><p>Hanazono-jinja (p102) hosts one every Sunday.</p><p>Hipster flea market Raw Tokyo is held over the</p><p>first weekend of the month at the Farmer’s Market</p><p>@UNU (p93). Quality vendors gather twice a</p><p>month at Tokyo International Forum (p28) for the</p><p>excellent Ōedo Antique Market.</p>', 3, '2024-12-19 03:57:23', '2024-12-19 03:57:23', 'tokyo-shopping'),
(18, 'Japan 711s are the best', '<p>They are cheap, conveniently placed everywhere and always open!</p>', 3, '2024-12-19 04:00:44', '2024-12-19 04:00:44', 'japan-711s-are-the-best'),
(19, 'Yokohama', '<p>Even though it’s just a 30-minute train ride south of central Tokyo, Yokohama (横浜) has an appealing flavour and history all its own. Locals are likely to cite the uncrowded, walkable streets or neighbourhood atmosphere as the main draw, but for visitors it’s the breezy bay front, creative arts scene, multiple microbreweries, jazz clubs and great international dining.</p>', 3, '2024-12-19 04:03:15', '2024-12-19 04:03:34', 'yokohama'),
(20, 'Hokkaido', '<p>Hokkaidō Hokkaidō is Japan’s northernmost island: a largely untamed, highly volcanic landscape marked by soaring peaks, crystal-clear lakes and steaming hot springs. Hikers, cyclists and road trippers are all drawn to the island’s big skies, wide open spaces and dramatic topography.</p>', 3, '2024-12-10 04:04:53', '2024-12-10 04:04:53', 'hokkaido');

-- --------------------------------------------------------

--
-- Table structure for table `post_categories`
--

CREATE TABLE `post_categories` (
  `post_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `post_categories`
--

INSERT INTO `post_categories` (`post_id`, `category_id`) VALUES
(1, 2),
(4, 3),
(10, 2),
(11, 9),
(12, 8),
(13, 2),
(16, 2),
(17, 4),
(18, 1),
(19, 8);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','client','visitor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `role`) VALUES
(3, 'asakura', 'sakura55@gmail.com', '$2y$10$MFwhyq3ARhTo7Q9gw19IJOrYq8C4HR6fN/k5Bcm/PTCGQPO9n9Fd2', 'client'),
(4, 'dsalangoste', 'dsalangoste@rrc.ca', '$2y$10$dR65pIWMrg6nPfrcTHPgu.hFWMUU18C.SgxXIa.w4fwGvgEFgqYe.', 'admin'),
(6, 'scottiebarnes', 'scottieb4@gmail.com', '$2y$10$QaRfaG/mPWWKLr/SCTSz.uDgCB5sf7V7TTQmKLXbPhP.4IGYWJ0Eq', 'client');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `post_id` (`post_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`image_id`),
  ADD KEY `post_id` (`post_id`);

--
-- Indexes for table `pages`
--
ALTER TABLE `pages`
  ADD PRIMARY KEY (`page_id`);

--
-- Indexes for table `page_categories`
--
ALTER TABLE `page_categories`
  ADD PRIMARY KEY (`page_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `page_comments`
--
ALTER TABLE `page_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `page_id` (`page_id`);

--
-- Indexes for table `posts`
--
ALTER TABLE `posts`
  ADD PRIMARY KEY (`post_id`),
  ADD KEY `author_id` (`author_id`);

--
-- Indexes for table `post_categories`
--
ALTER TABLE `post_categories`
  ADD PRIMARY KEY (`post_id`,`category_id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `comments`
--
ALTER TABLE `comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `image_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pages`
--
ALTER TABLE `pages`
  MODIFY `page_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `page_comments`
--
ALTER TABLE `page_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `posts`
--
ALTER TABLE `posts`
  MODIFY `post_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `images`
--
ALTER TABLE `images`
  ADD CONSTRAINT `images_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE;

--
-- Constraints for table `page_categories`
--
ALTER TABLE `page_categories`
  ADD CONSTRAINT `page_categories_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`page_id`),
  ADD CONSTRAINT `page_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`);

--
-- Constraints for table `page_comments`
--
ALTER TABLE `page_comments`
  ADD CONSTRAINT `page_comments_ibfk_1` FOREIGN KEY (`page_id`) REFERENCES `pages` (`page_id`);

--
-- Constraints for table `posts`
--
ALTER TABLE `posts`
  ADD CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`author_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `post_categories`
--
ALTER TABLE `post_categories`
  ADD CONSTRAINT `post_categories_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`post_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `post_categories_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
