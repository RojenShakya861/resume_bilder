import joblib
import os
import pandas as pd

def load_model():
    """Load the trained model and label encoder"""
    model_path = os.path.join(os.path.dirname(__file__), 'models', 'skill_model.joblib')
    encoder_path = os.path.join(os.path.dirname(__file__), 'models', 'label_encoder.joblib')
    
    model = joblib.load(model_path)
    label_encoder = joblib.load(encoder_path)
    
    return model, label_encoder

def predict_job(skills):
    """Predict job title based on skills"""
    model, label_encoder = load_model()
    
    # Convert skills to binary features
    all_skills = model.feature_names_in_
    skill_vector = pd.DataFrame([[1 if skill in skills else 0 for skill in all_skills]], 
                              columns=all_skills)
    
    # Make prediction
    prediction = model.predict(skill_vector)
    job_title = label_encoder.inverse_transform(prediction)[0]
    
    # Get confidence scores
    confidence_scores = model.predict_proba(skill_vector)[0]
    max_confidence = max(confidence_scores)
    
    return job_title, max_confidence

def main():
    # Test cases
    test_cases = [
        ["Python", "JavaScript", "SQL", "HTML", "CSS", "Git"],
        ["HTML", "CSS", "JavaScript", "React", "Node.js"],
        ["Python", "R", "SQL", "Statistics", "Machine Learning"],
        ["Linux", "Docker", "Kubernetes", "AWS", "CI/CD"],
        ["Figma", "Adobe XD", "HTML", "CSS", "User Research"]
    ]
    
    print("Testing model predictions:")
    print("-" * 50)
    
    for i, skills in enumerate(test_cases, 1):
        job_title, confidence = predict_job(skills)
        print(f"\nTest Case {i}:")
        print(f"Skills: {', '.join(skills)}")
        print(f"Predicted Job: {job_title}")
        print(f"Confidence: {confidence:.2%}")

if __name__ == '__main__':
    main() 