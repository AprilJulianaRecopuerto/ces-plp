import unittest
from unittest.mock import patch, MagicMock
from app import app

class FlaskTestCase(unittest.TestCase):

    @patch('app.get_db_connection')
    def test_recommended_events(self, mock_get_db_connection):
        # Mock the database connection and cursor
        mock_connection = MagicMock()
        mock_cursor = MagicMock()
        mock_get_db_connection.return_value = mock_connection
        mock_connection.cursor.return_value = mock_cursor

        # Simulate a database response
        mock_cursor.fetchall.return_value = [
            ('College of Arts and Sciences', 'Event 1', 4.5),
            ('College of Arts and Sciences', 'Event 2', 4.2)
        ]

        # Test the /get_recommended_events route
        with app.test_client() as client:
            response = client.get('/get_recommended_events/College of Arts and Sciences')

            # Check the response status code
            self.assertEqual(response.status_code, 200)

            # Check if the correct content is returned (you can customize this based on your HTML structure)
            self.assertIn(b'Recommended Events for College of Arts and Sciences', response.data)
            self.assertIn(b'Event 1', response.data)
            self.assertIn(b'Rating: 100.0%', response.data)

if __name__ == '__main__':
    unittest.main()
