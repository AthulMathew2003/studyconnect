# Use an official Python image as a base
FROM python:3.11-slim

# Set the working directory
WORKDIR /app

# Copy the contents of the current directory to /app
COPY . .

# Install dependencies (if there's a requirements.txt file)
RUN pip install --no-cache-dir -r requirements.txt || true

# Set the command to run your app (adjust if needed)
CMD ["python", "main.py"]
