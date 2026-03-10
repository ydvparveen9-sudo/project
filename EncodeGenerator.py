import cv2
import face_recognition
import pickle
import os

# 1. Folder Path jahan photos rakhi hain
folderPath = 'image' 
pathList = os.listdir(folderPath)
imgPaths = []
studentIds = []

# 2. Photos aur IDs ko load karna
VALID_EXTENSIONS = ('.jpg', '.jpeg', '.png', '.bmp', '.tiff', '.tif')
for path in pathList:
    if not path.lower().endswith(VALID_EXTENSIONS):
        print(f"Skipping non-image file: {path}")
        continue
    fullPath = os.path.join(folderPath, path)
    imgPaths.append(fullPath)
    studentIds.append(os.path.splitext(path)[0]) # File ka naam ID ban jayega

print(f"Total Images Found: {len(imgPaths)}")
print(f"Student IDs: {studentIds}")

# 3. Encoding function
def findEncodings(imagePaths):
    encodeList = []
    for i, imgPath in enumerate(imagePaths):
        img = face_recognition.load_image_file(imgPath)  # loads as RGB uint8 directly
        encodings = face_recognition.face_encodings(img)
        if len(encodings) == 0:
            print(f"Warning: No face found in {imgPath}, skipping.")
            continue
        encodeList.append(encodings[0])
        print(f"  Encoded: {imgPath}")
    return encodeList

print("Encoding Started ...")
encodeListKnown = findEncodings(imgPaths)
encodeListKnownWithIds = [encodeListKnown, studentIds]
print("Encoding Complete")

# 4. Encodings ko file mein save karna
file = open("EncodeFile.p", 'wb')
pickle.dump(encodeListKnownWithIds, file)
file.close()
print("File Saved as EncodeFile.p")