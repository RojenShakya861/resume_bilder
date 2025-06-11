-- Add template_id column to resumes table
ALTER TABLE resumes
ADD COLUMN template_id INT(11) NOT NULL DEFAULT 1 AFTER user_id;

-- Add foreign key constraint to templates table (if templates table exists)
ALTER TABLE resumes
ADD CONSTRAINT fk_resume_template
FOREIGN KEY (template_id) REFERENCES templates(id)
ON DELETE RESTRICT
ON UPDATE CASCADE; 