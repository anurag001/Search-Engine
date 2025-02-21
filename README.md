# CrawlX - Web Crawler & Search Engine

CrawlX is a simple web crawler and search engine that retrieves web page content based on user queries. It follows a **Breadth-First Search (BFS) approach** to crawl Wikipedia pages and other websites. Additionally, it supports web search and image search functionalities.

## Features

- **Web Crawling:** Extracts content and metadata from Wikipedia and other websites.
- **Search Engine:** Users can search for keywords, and the crawler fetches relevant results.
- **BFS-Based Crawling:** Follows links iteratively but may encounter timeouts due to depth limitations.
- **Image Search:** Fetches images related to search queries.
- **Backlink Analysis:** Retrieves backlinks using Google search API.

## Live Demo

[Visit CrawlX](http://crawlx.freevar.com/)

## How It Works

1. The user enters a search keyword.
2. The crawler starts from Wikipedia and follows links using a BFS approach.
3. The results are stored in a database and displayed to the user.
4. Users can also perform an image search related to the keyword.

## Technologies Used

- **PHP** - Backend logic and data processing
- **MySQL** - Database to store crawled data
- **cURL & DOMDocument** - Fetching and parsing HTML content
- **JavaScript & jQuery** - Frontend interactivity
- **Bootstrap** - Responsive UI design

## Installation

1. Clone the repository:
   ```sh
   git clone https://github.com/yourusername/crawlx.git
   ```
2. Set up a local or remote server with PHP and MySQL.
3. Import the database schema from `database.sql`.
4. Update database connection details in `config.php`.
5. Run the project on your server.

## Contributing

We welcome contributions! If you’d like to improve CrawlX, feel free to:

- Report issues
- Submit pull requests
- Enhance crawling efficiency and search results
- Improve UI/UX

Fork the project and start contributing!

## License

This project is open-source and available under the **MIT License**.

---

🌟 **Star the repository if you find it useful!**
