import pandas as pd
import numpy as np
from sklearn.tree import DecisionTreeClassifier
from sklearn.preprocessing import LabelEncoder
import joblib
import os

def load_dataset():
    """Load and preprocess the skills dataset"""
    print("Loading dataset...")
    
    # Create sample dataset if it doesn't exist
    if not os.path.exists('data/skills_dataset.csv'):
        os.makedirs('data', exist_ok=True)
        
        # Create sample data
        data = {
            'job_title': [
                'Software Developer', 'Software Developer', 'Software Developer',
                'Data Scientist', 'Data Scientist', 'Data Scientist',
                'Web Developer', 'Web Developer', 'Web Developer',
                'DevOps Engineer', 'DevOps Engineer', 'DevOps Engineer',
                'Mobile Developer', 'Mobile Developer', 'Mobile Developer'
            ],
            'skills': [
                ['Python', 'JavaScript', 'SQL', 'Git', 'REST API'],
                ['Java', 'Spring', 'SQL', 'Git', 'Maven'],
                ['C#', '.NET', 'SQL', 'Git', 'Azure'],
                ['Python', 'R', 'SQL', 'Statistics', 'Machine Learning'],
                ['Python', 'SQL', 'Data Analysis', 'Statistics', 'Tableau'],
                ['Python', 'SQL', 'Deep Learning', 'TensorFlow', 'PyTorch'],
                ['HTML', 'CSS', 'JavaScript', 'React', 'Node.js'],
                ['HTML', 'CSS', 'JavaScript', 'Vue.js', 'PHP'],
                ['HTML', 'CSS', 'JavaScript', 'Angular', 'TypeScript'],
                ['Linux', 'Docker', 'Kubernetes', 'AWS', 'CI/CD'],
                ['Linux', 'Docker', 'Jenkins', 'Terraform', 'Ansible'],
                ['Linux', 'Docker', 'Azure', 'Puppet', 'Shell Scripting'],
                ['Java', 'Kotlin', 'Android', 'REST API', 'Git'],
                ['Swift', 'iOS', 'Xcode', 'REST API', 'Git'],
                ['React Native', 'JavaScript', 'Mobile UI', 'REST API', 'Git']
            ]
        }
        
        # Convert to DataFrame
        df = pd.DataFrame(data)
        df.to_csv('data/skills_dataset.csv', index=False)
        print("Created sample dataset")
    
    # Load the dataset
    df = pd.read_csv('data/skills_dataset.csv')
    
    # Convert skills string to list
    df['skills'] = df['skills'].apply(eval)
    
    # Get all unique skills
    all_skills = set()
    for skills in df['skills']:
        all_skills.update(skills)
    
    # Create binary features for each skill
    for skill in all_skills:
        df[skill] = df['skills'].apply(lambda x: 1 if skill in x else 0)
    
    # Prepare features and target
    X = df.drop(['job_title', 'skills'], axis=1)
    y = df['job_title']
    
    # Encode job titles
    label_encoder = LabelEncoder()
    y_encoded = label_encoder.fit_transform(y)
    
    print(f"Dataset loaded with {len(df)} samples and {len(all_skills)} unique skills")
    return X, y_encoded, label_encoder

def train_model():
    """Train and save the decision tree model"""
    try:
        # Load and preprocess data
        X, y, label_encoder = load_dataset()
        
        # Create and train the model
        print("Training model...")
        model = DecisionTreeClassifier(random_state=42)
        model.fit(X, y)
        
        # Create models directory if it doesn't exist
        os.makedirs('models', exist_ok=True)
        
        # Save the model and label encoder
        print("Saving model and encoder...")
        joblib.dump(model, 'models/skill_model.joblib')
        joblib.dump(label_encoder, 'models/label_encoder.joblib')
        
        print("Model training completed successfully!")
        
        # Print feature importance
        feature_importance = pd.DataFrame({
            'skill': X.columns,
            'importance': model.feature_importances_
        }).sort_values('importance', ascending=False)
        
        print("\nTop 10 most important skills:")
        print(feature_importance.head(10))
        
    except Exception as e:
        print(f"Error during model training: {str(e)}")
        raise

if __name__ == '__main__':
    train_model() 