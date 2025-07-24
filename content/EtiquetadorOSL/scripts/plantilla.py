from fillpdf import fillpdfs
import os

# Make sure this path is correct relative to where you run the script
pdf_template_path = "pdf/plantilla.pdf"

if os.path.exists(pdf_template_path):
    fields = fillpdfs.get_form_fields(pdf_template_path)
    print("--- PDF Form Fields ---")
    for field_name, field_value in fields.items(): # Changed field_info to field_value
        print(f"Field Name: {field_name}")
        print(f"  Current Value: {field_value}") # Now print the direct value
        # In this scenario, 'type' and 'options' won't be directly available this way
        # You'll have to infer type (e.g., if it has options, it's a checkbox/radio)
        print("-" * 20)
else:
    print(f"Error: Template PDF not found at {pdf_template_path}")
