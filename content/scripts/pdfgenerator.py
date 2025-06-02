import os
import sys
import shutil
import re
import pymupdf
from fillpdf import fillpdfs
import qrcode


class PdfCreator:
    def __init__(self):
        """
        Constructor que se encarga de limpiar archivos previos y 
        procesar los argumentos pasados al script.
        """
        
        self.parse_arguments()
        if self.clean == "true":
            self.cleanup(False)

    def cleanup(self, remove_also_generado: bool):
        """
        Elimina el archivo 'pdf/generado.pdf' y elimina recursivamente la carpeta 'pdf/raid' 
        en caso de que existan, ignorando errores si no se encuentran.
        """
        if remove_also_generado:
            try:
                os.remove("pdf/generado.pdf")
            except Exception:
                pass
        
        try:
            for name in os.listdir("pdf/raid"):
                full_path = os.path.join("pdf/raid", name)
                if os.path.isfile(full_path) or os.path.islink(full_path):
                    os.unlink(full_path)  # Elimina archivos y enlaces simbólicos
                elif os.path.isdir(full_path):
                    shutil.rmtree(full_path)  # Elimina subcarpetas
        except Exception:
            pass
        
        try:
            for name in os.listdir("pdf/qrcodes"):
                full_path = os.path.join("pdf/qrcodes", name)
                if os.path.isfile(full_path) or os.path.islink(full_path):
                    os.unlink(full_path)  # Elimina archivos y enlaces simbólicos
                elif os.path.isdir(full_path):
                    shutil.rmtree(full_path)  # Elimina subcarpetas
        except Exception:
            pass

        try: 
            for name in os.listdir("pdf/finalraid"):
                full_path = os.path.join("pdf/finalraid", name)
                if os.path.isfile(full_path) or os.path.islink(full_path):
                    os.unlink(full_path)  # Elimina archivos y enlaces simbólicos
                elif os.path.isdir(full_path):
                    shutil.rmtree(full_path)  # Elimina subcarpetas
        except Exception:
            pass

    @staticmethod
    def padding_num(num) -> str:
        """
        Recibe un número y retorna una cadena con el número formateado a 4 dígitos.
        
        Ejemplo:
            padding_num(5)  --> "0005"
            padding_num(123) --> "0123"
        """
        return str(num).zfill(4)
    
    @staticmethod
    def extract_num(name):
        return int(re.search(r'\d+', name).group())
    
    def parse_arguments(self):
        """
        Procesa los argumentos recibidos en sys.argv y asigna los valores 
        a los atributos correspondientes de la clase.
        """
        # Los argumentos se esperan en el siguiente orden:
        # 1. board_type: 'bios' o 'uefi' (1 para bios, 2 para uefi)
        # 2. cpu_name: Nombre de la CPU (se asigna cadena vacía si es "Indefinido")
        # 3. ram_capacity: Capacidad de la RAM
        # 4. ram_type: 'ddr4', 'ddr3', 'ddr2' o de otro tipo (1, 2, 3 u 4 respectivamente)
        # 5. disc_type: 'hdd', 'ssd' o de otro tipo (1, 2 o 3 respectivamente)
        # 6. disc_capacity: Capacidad del disco (se asigna cadena vacía si es "Indefinido")
        # 7. gpu_name: Nombre de la GPU (se asigna cadena vacía si es "Indefinido")
        # 8. gpu_type: 'integrada' (1) o de otro tipo (2)
        # 9. wifi: 'true' (1) o de otro valor (2)
        # 10. bluetooth: 'true' (1) o de otro valor (2)
        # 11. sn_prefix: Prefijo del serial number
        # 12. sn_num: Número a formatear para el serial number
        # 13. name: Nombre del archivo
        # 14. end: Indica cuando ha terminado de crear los pdfs desde php para poder ejecutar merge_pdfs()
        # 15. clean: Indica cuando debe limpiar el contenido de la carpeta pdf/raid
        # 16. observaciones: (opcional) Observaciones adicionales

        self.board_type = 1 if sys.argv[1] == 'bios' else 2
        self.cpu_name = sys.argv[2]if sys.argv[2] != "Indefinido" else ""
        self.ram_capacity = sys.argv[3]
        self.ram_type = sys.argv[4]

        # Determina el tipo de disco
        disc_arg = sys.argv[5]
        if disc_arg == 'hdd':
            self.disc_type = 1
        elif disc_arg == 'ssd':
            self.disc_type = 2
        else:
            self.disc_type = 3

        self.disc_capacity = sys.argv[6] if sys.argv[6] != "Indefinido" else ""
        self.gpu_name = sys.argv[7] if sys.argv[7] != "Indefinido" else ""
        self.gpu_type = 1 if sys.argv[8] == 'integrada' else 2
        self.wifi = 1 if sys.argv[9] == 'true' else 2
        self.bluetooth = 1 if sys.argv[10] == 'true' else 2

        self.sn_prefix = sys.argv[11]
        self.sn_num = str(sys.argv[12])
        self.sn = f"{self.sn_prefix}-{self.padding_num(self.sn_num)}"
        self.name = sys.argv[13]
        self.qrcode_name = sys.argv[14]
        self.end = sys.argv[15]
        self.is_single = sys.argv[16]
        self.clean = sys.argv[17]
        self.pc_url = sys.argv[18]
        try:
            self.observaciones = sys.argv[19]
        except IndexError:
            self.observaciones = ""

    def debug_info(self):
        """
        Imprime información de depuración basada en los argumentos procesados 
        y finaliza la ejecución del programa.
        """

        print("=== Debug Info ===")
        print(f"Board Type     : {self.board_type}")
        print(f"CPU Name       : {self.cpu_name}")
        print(f"RAM Capacity   : {self.ram_capacity}")
        print(f"RAM Type       : {self.ram_type}")
        print(f"Disk Capacity  : {self.disc_capacity}")
        print(f"GPU Name       : {self.gpu_name}")
        print(f"GPU Type       : {self.gpu_type}")
        print(f"WiFi           : {self.wifi}")
        print(f"Bluetooth      : {self.bluetooth}")
        print(f"Observaciones  : {self.observaciones}")
        print(f"SN Prefix      : {self.sn_prefix}")
        print(f"SN Number      : {self.sn_num}")
        print(f"Serial Number  : {self.sn}")
        print(f"Label Name     : {self.name}")
        print("==================")
        sys.exit()

    def create_qrcode(self):
        qr = qrcode.QRCode(
            version=1,
            error_correction=qrcode.constants.ERROR_CORRECT_H,
            box_size=10,
            border=0,
        )
        qr.add_data(self.pc_url)

        img = qr.make_image(fit=False)
        return img

    def create_pdf(self):
        """
        Crea el PDF utilizando la plantilla y un diccionario con los valores recopilados a partir de los argumentos.
        """

        data = {
            'sn': self.sn,
            'board_type': self.board_type,                  # 1: BIOS, 2: UEFI
            'cpu_name': self.cpu_name.upper(),              # Nombre de la CPU
            'ram_type': self.ram_type.upper(),              # Tipo de RAM (1: DDR4, 2: DDR3, etc.)
            'ram_capacity': self.ram_capacity,              # Capacidad de la RAM en GB
            'disc_type': self.disc_type,                    # Tipo de disco (1: HDD, 2: SSD, 3: NVMe)
            'disc_capacity': self.disc_capacity,            # Capacidad del disco en GB 
            'gpu_type': self.gpu_type,                      # Tipo de GPU (1: integrada, 2: externa)
            'gpu_name': self.gpu_name.upper(),              # Nombre de la GPU
            'wifi_bool': self.wifi,                         # 1 si tiene WiFi, 2 si no
            'bluetooth_bool': self.bluetooth,               # 1 si tiene Bluetooth, 2 si no
            'obser': self.observaciones                     # Observaciones (texto libre)
        }
        # Se crea el PDF utilizando la plantilla "pdf/plantilla.pdf"
        # El archivo generado tendrá como nombre la variable name
        fillpdfs.write_fillable_pdf(input_pdf_path="pdf/plantilla.pdf", output_pdf_path=f"{self.name}", data_dict=data, flatten=True)
        
        qrcode = self.create_qrcode()
        qrcode.save(self.qrcode_name)
        
        if self.end == "true":
            self.merge_pdfs()

            

    def merge_pdfs(self):
        qrcode_folder = "pdf/qrcodes"
        raid_folder = "pdf/raid"
        output_path = "pdf/generado.pdf"

        pdf_files = [f for f in os.listdir(raid_folder) if f.endswith('.pdf')]
        pdf_files = sorted(pdf_files, key=self.extract_num)

        qrcodes = [f for f in os.listdir(qrcode_folder) if f.endswith('.png')]
        qrcodes = sorted(qrcodes, key=self.extract_num)

        if not pdf_files:
            print("No hay archivos PDF para combinar.")
            return



        for index in range(len(pdf_files)):

            full_path_pdf = os.path.join(raid_folder, pdf_files[index])
            full_path_qrcode = os.path.join(qrcode_folder, qrcodes[index])

            # Agregar el codigo QR
            pdf = pymupdf.open(f"{full_path_pdf}")
            page = pdf[0]
            rect = pymupdf.Rect(16, 221, 53, 258)
            page.insert_image(rect, filename=full_path_qrcode)

            pdf.save(f"pdf/finalraid/generado{index+1}.pdf")
        pdf.close()

        merger = pymupdf.open()
        pdf_files = [f for f in os.listdir("pdf/finalraid") if f.endswith('.pdf')]
        pdf_files = sorted(pdf_files, key=self.extract_num)

        for pdf_file in pdf_files:
            full_path_pdf = os.path.join("pdf/finalraid", pdf_file)
            merger.insert_file(full_path_pdf)

        merger.save(output_path)
        merger.close()

        

if __name__ == "__main__":
    pdf_creator = PdfCreator()

    # Si deseas ver la información antes de generar el PDF, descomenta esta línea:
    # pdf_creator.debug_info()

    pdf_creator.create_pdf()
