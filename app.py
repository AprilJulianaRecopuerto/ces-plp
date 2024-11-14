from flask import Flask, render_template
import mysql.connector

app = Flask(__name__)

# Function to get database connection
def get_db_connection():
    connection = mysql.connector.connect(
        host='localhost',
        user='root',  # Replace with your database username
        password='',  # Replace with your database password
        database='certificate'  # Replace with your database name
    )
    return connection

# Endpoint to render the recommended events page for each department
@app.route('/get_recommended_events/<department>', methods=['GET'])
def recommended_events(department):
    # Connect to the database
    connection = get_db_connection()
    cursor = connection.cursor()

    # Query to get the highest-rated events for other departments, allowing multiple events with the same rating, but only those >= 4
    query = """
        WITH ranked_events AS (
            SELECT department, event, AVG(rate) AS average_rating
            FROM submissions
            WHERE department != %s  -- Exclude the current department
            GROUP BY department, event
        ),
        max_rated_events AS (
            SELECT department, MAX(average_rating) AS max_rating
            FROM ranked_events
            WHERE average_rating >= 4  -- Only consider events with average rating >= 4
            GROUP BY department
        )
        SELECT r.department, r.event, r.average_rating
        FROM ranked_events r
        JOIN max_rated_events m ON r.department = m.department
        WHERE r.average_rating = m.max_rating
        AND r.average_rating >= 4  -- Ensure that the rating is >= 4
        ORDER BY r.department;
    """
    
    cursor.execute(query, (department,))
    events = cursor.fetchall()

    # Debugging output: Print the SQL result
    print(f"Department: {department}")
    print(f"Events fetched from database: {events}")

    cursor.close()
    connection.close()

    # Convert the data into a list of dictionaries for easier access in the front end
    event_list = [{'department': event[0], 'event': event[1], 'average_rating': round(event[2] * 100, 2)} for event in events]

    # Render the template and pass the events and department to the template
    return render_template('recommendations.html', department=department, events=event_list)

# Run the app
if __name__ == '__main__':
    app.run(debug=True)