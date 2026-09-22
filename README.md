# RAG Knowledge Factory AI

A full-stack Retrieval-Augmented Generation platform built with Laravel, Gemini, and Qdrant.

The system can ingest documents, images, large PDF books, and public website content, convert the extracted knowledge into semantic chunks, generate embeddings, store vectors in Qdrant, and answer questions using the retrieved knowledge.

## Features

* Universal knowledge ingestion
* PDF ingestion
* DOCX ingestion
* TXT and Markdown ingestion
* CSV, JSON, XML, and HTML ingestion
* XLSX spreadsheet ingestion
* PPTX presentation ingestion
* JPG, JPEG, PNG, and WebP image ingestion
* Large PDF and book processing
* Gemini-powered image and scanned-document understanding
* Automatic text extraction
* Semantic text chunking
* Batch embedding generation
* Qdrant vector storage
* Vector similarity retrieval
* RAG-based question answering
* Website crawling and ingestion
* Same-domain page discovery
* Sitemap discovery
* Website content extraction
* Website page chunking and embedding
* Source-aware responses
* Public browser chatbot
* External REST API
* Secure generated API keys
* API key enable, disable, and delete controls
* Dynamic public chatbot URL
* Knowledge source deletion
* Selective Qdrant vector deletion
* 3D futuristic knowledge factory interface

## Technology Stack

### Backend

* Laravel 13
* PHP 8.4
* Laravel HTTP Client
* SQLite

### Artificial Intelligence

* Gemini 3.1 Flash-Lite
* Gemini Embedding API
* Retrieval-Augmented Generation
* Semantic Search
* Multimodal file understanding

### Vector Database

* Qdrant
* Cosine similarity
* 768-dimensional embeddings
* Docker-based local Qdrant instance

### Document Processing

* Smalot PDF Parser
* PHPWord
* ZipArchive
* DOMDocument
* DOMXPath

### Frontend

* Blade
* HTML
* CSS
* JavaScript
* Custom 3D glassmorphism UI

## System Architecture

```text
Knowledge Source
      |
      v
Text / Knowledge Extraction
      |
      v
Cleaning
      |
      v
Semantic Chunking
      |
      v
Gemini Embeddings
      |
      v
Qdrant Vector Database
      |
      v
Similarity Search
      |
      v
Relevant Chunks
      |
      v
Gemini
      |
      v
Final Answer
```

## Supported Knowledge Sources

### Documents

```text
PDF
DOCX
TXT
MD
CSV
JSON
XML
HTML
HTM
XLSX
PPTX
```

### Images

```text
JPG
JPEG
PNG
WEBP
```

Images are analyzed using Gemini so visible text and useful visual information can become searchable knowledge.

## Large Document Processing

Large documents and books are divided into multiple semantic chunks before embedding.

Embeddings are generated in batches and stored incrementally inside Qdrant.

Example:

```text
500 Page Book
      |
      v
Extract Text
      |
      v
Create Hundreds of Chunks
      |
      v
Batch Embeddings
      |
      v
Qdrant
      |
      v
Ask Questions
```

The complete book does not need to be sent to the language model for every question.

Only the most relevant chunks are retrieved.

## Website Knowledge Factory

The platform also supports website-based knowledge ingestion.

A user can provide a public website URL and configure the maximum number of pages to crawl.

Pipeline:

```text
Website URL
      |
      v
Connect Website
      |
      v
Discover Pages
      |
      v
Extract Public Page Content
      |
      v
Clean Text
      |
      v
Create Chunks
      |
      v
Generate Embeddings
      |
      v
Store in Qdrant
```

The crawler supports:

* Same-domain crawling
* Sitemap discovery
* Internal link discovery
* HTML content extraction
* Navigation and unnecessary page element removal
* Page title preservation
* Page URL preservation
* Maximum page limits
* Website-specific vector deletion

Website vectors contain metadata such as:

```text
website_source_id
source_type
source_url
website_host
page_title
chunk_index
text
```

## Knowledge Ingestion Factory

The frontend provides a visual AI ingestion pipeline.

During file ingestion, the interface displays stages such as:

```text
Extract
Chunk
Embed
Qdrant
Ready
```

Website ingestion provides:

```text
Connect
Discover
Extract
Chunk
Embed
Store
```

The interface contains a minimum visual processing sequence while the real Laravel ingestion engine runs in the backend.

## Qdrant Storage

All embeddings are stored inside the configured Qdrant collection.

Document payload example:

```json
{
    "document_id": 1,
    "file_name": "example.pdf",
    "file_type": "pdf",
    "source_type": "document",
    "chunk_index": 0,
    "text": "Extracted knowledge..."
}
```

Website payload example:

```json
{
    "website_source_id": 1,
    "source_type": "website",
    "source_url": "https://example.com/about",
    "website_host": "example.com",
    "page_title": "About",
    "chunk_index": 0,
    "text": "Extracted website knowledge..."
}
```

## RAG Chatbot

When a user sends a question:

```text
Question
    |
    v
Gemini Embedding
    |
    v
Qdrant Search
    |
    v
Top Relevant Chunks
    |
    v
Gemini 3.1 Flash-Lite
    |
    v
Answer
```

The assistant is configured to answer naturally without exposing internal RAG implementation details during normal conversation.

## Public Chatbot

The application provides a standalone browser chatbot.

Local URL:

```text
http://127.0.0.1:8000/chatbot/public
```

When deployed, Laravel automatically generates the production URL using the application domain.

The public chatbot uses the same knowledge base and RAG engine as the main dashboard.

## External Chat API

External applications can access the RAG assistant through:

```text
POST /api/external/chat
```

Example request:

```json
{
    "question": "Ask something from the knowledge base"
}
```

Required header:

```text
X-API-Key: YOUR_GENERATED_API_KEY
```

Example:

```bash
curl -X POST http://127.0.0.1:8000/api/external/chat \
-H "Content-Type: application/json" \
-H "X-API-Key: YOUR_GENERATED_API_KEY" \
-d "{\"question\":\"Ask something\"}"
```

## API Key System

The application supports generating external API keys.

Keys use the following format:

```text
rag_live_xxxxxxxxxxxxxxxxxxxxxxxxx
```

Only the generated key is returned to the user.

The application stores a SHA-256 hash instead of storing the full API key.

Available controls:

* Generate API key
* Copy API key
* Enable key
* Disable key
* Delete key
* Track last use

## Environment Configuration

Add the required configuration to `.env`.

```env
GEMINI_API_KEY=YOUR_GEMINI_API_KEY
GEMINI_CHAT_MODEL=gemini-3.1-flash-lite
GEMINI_EMBEDDING_MODEL=gemini-embedding-001

QDRANT_URL=http://127.0.0.1:6333
QDRANT_COLLECTION=rag_documents
```

Never commit the real `.env` file or API keys to GitHub.

## Installation

Clone the project:

```bash
git clone https://github.com/muhammadzayed2003/RAG-Knowledge-Factory-AI.git
```

Enter the project:

```bash
cd RAG-Knowledge-Factory-AI
```

Install PHP dependencies:

```bash
composer install
```

Create environment configuration:

```bash
copy .env.example .env
```

Generate Laravel application key:

```bash
php artisan key:generate
```

Run migrations:

```bash
php artisan migrate
```

Clear configuration cache:

```bash
php artisan optimize:clear
```

## Start Qdrant

Docker Desktop must be running.

If the Qdrant container already exists:

```bash
docker start qdrant
```

Check runni
