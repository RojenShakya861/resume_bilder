from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import pandas as pd
import os

app = Flask(__name__)
CORS(app)  # Enable CORS for all routes

# Load the trained model and label encoder
try:
    model = joblib.load('models/skill_model.joblib')
    label_encoder = joblib.load('models/label_encoder.joblib')
    print("Model loaded successfully!")
except FileNotFoundError:
    print("Error: Model files not found. Please train the model first.")
    model = None
    label_encoder = None

@app.route('/predict', methods=['POST'])
def predict():
    """Predict job title based on skills"""
    if model is None or label_encoder is None:
        return jsonify({
            'error': 'Model not loaded. Please train the model first.',
            'status': 'error'
        }), 500

    try:
        data = request.get_json()
        if not data or 'skills' not in data:
            return jsonify({
                'error': 'No skills provided',
                'status': 'error'
            }), 400

        # Convert user skills to binary features
        user_skills = data['skills']
        skill_vector = pd.DataFrame([[1 if skill in user_skills else 0 for skill in model.feature_names_in_]], 
                                  columns=model.feature_names_in_)

        # Make prediction
        prediction = model.predict(skill_vector)
        job_title = label_encoder.inverse_transform(prediction)[0]

        # Get confidence scores
        confidence_scores = model.predict_proba(skill_vector)[0]
        max_confidence = max(confidence_scores)

        return jsonify({
            'job_title': job_title,
            'confidence': float(max_confidence),
            'status': 'success'
        })

    except Exception as e:
        return jsonify({
            'error': str(e),
            'status': 'error'
        }), 500

@app.route('/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'healthy',
        'model_loaded': model is not None
    })

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True) 