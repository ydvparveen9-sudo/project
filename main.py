import os
import pickle
import numpy as np
import cv2
import face_recognition
import cvzone
import mysql.connector
from datetime import datetime

# 1. Localhost Database Connection
db = mysql.connector.connect(
    host="localhost",
    user="root",
    password="", 
    database="attendance_db"
)
cursor = db.cursor(dictionary=True)

# 2. Camera Setup
cap = cv2.VideoCapture(0) # Camera on karne ke liye
cap.set(3, 640)
cap.set(4, 480)

# 3. Load Encoding File (Jo aapne pehle banayi hogi)
print("Loading Encode File ...")
if os.path.exists('EncodeFile.p'):
    file = open('EncodeFile.p', 'rb')
    encodeListKnownWithIds = pickle.load(file)
    file.close()
    encodeListKnown, studentIds = encodeListKnownWithIds
    print("Encode File Loaded")
else:
    print("Error: EncodeFile.p nahi mili! Pehle EncodeGenerator.py chalayein.")

counter = 0
id = -1

while True:
    success, img = cap.read()
    imgS = cv2.resize(img, (0, 0), None, 0.25, 0.25)
    imgS = cv2.cvtColor(imgS, cv2.COLOR_BGR2RGB)

    faceCurFrame = face_recognition.face_locations(imgS)
    encodeCurFrame = face_recognition.face_encodings(imgS, faceCurFrame)

    if faceCurFrame:
        for encodeFace, faceLoc in zip(encodeCurFrame, faceCurFrame):
            matches = face_recognition.compare_faces(encodeListKnown, encodeFace)
            faceDis = face_recognition.face_distance(encodeListKnown, encodeFace)
            matchIndex = np.argmin(faceDis)

            if matches[matchIndex]:
                id = studentIds[matchIndex]
                if counter == 0:
                    counter = 1

        if counter != 0:
            if counter == 1:
                # Database se student data lena
                cursor.execute("SELECT * FROM students WHERE id = %s", (id,))
                studentInfo = cursor.fetchone()
                
                # Attendance update logic (30 seconds gap)
                if studentInfo:
                    last_time = studentInfo['last_attendance_time']
                    if last_time is None or (datetime.now() - last_time).total_seconds() > 30:
                        new_total = studentInfo['total_attendance'] + 1
                        now = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
                        cursor.execute("UPDATE students SET total_attendance = %s, last_attendance_time = %s WHERE id = %s", 
                                       (new_total, now, id))
                        db.commit()
                        print(f"Attendance Marked for: {studentInfo['name']}")
                    else:
                        print("Attendance already marked recently.")
                
                counter = 0 

    cv2.imshow("Face Attendance System", img)
    if cv2.waitKey(1) & 0xFF == ord('q'): # 'q' dabane par band hoga
        break

db.close()
cap.release()
cv2.destroyAllWindows()