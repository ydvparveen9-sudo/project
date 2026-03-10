import mysql.connector

# 1. Database se connection banayein
try:
    db = mysql.connector.connect(
        host="localhost",
        user="root",
        password="", # XAMPP mein password aksar khali hota hai
        database="attendance_db"
    )
    cursor = db.cursor()

    # 2. SQL Query taiyar karein
    sql = "INSERT INTO students (id, name, major, starting_year, total_attendance, standing, year) VALUES (%s, %s, %s, %s, %s, %s, %s)"
    
    # 3. Students ka data (Aap ise badal sakte hain)
    data = [
        ("321654", "Murtaza Hassan", "Robotics", 2017, 7, "G", 4),
        ("852741", "Emily Blunt", "Economics", 2021, 12, "B", 1),
        ("963852", "Elon Musk", "Physics", 2020, 7, "G", 2)
    ]

    # 4. Data insert karein
    cursor.executemany(sql, data)
    db.commit() # Changes ko save karne ke liye

    print(f"{cursor.rowcount} students successfully added to Localhost!")

except mysql.connector.Error as err:
    print(f"Kuch galti hui hai: {err}")

finally:
    if db.is_connected():
        cursor.close()
        db.close()