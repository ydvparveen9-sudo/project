"""
FILE OVERVIEW:
- Quick Python sanity script: project-level testing/debug ke liye simple runner.
- Is file ka code flow samajhne ke liye niche ke functions/steps ko sequence me padhein.
"""

import os
import runpy


if __name__ == "__main__":
    base_dir = os.path.dirname(os.path.abspath(__file__))
    attendance_main = os.path.join(base_dir, "attendance", "main.py")
    runpy.run_path(attendance_main, run_name="__main__")



