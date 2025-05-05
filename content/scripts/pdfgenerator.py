from fillpdf import fillpdfs
import sys, os
try:
    os.remove("scripts/pdf/generado.pdf")
except:
    pass

def padding_num(num):
    num = str(num)
    for i in range(len(num), 4):
        num = "0" + str(num)
    return num

# Obtener los datos de los argumentos
board_type      = 1 if sys.argv[1] == 'bios' else 2                                                                      # 1 para 'bios', 2 para 'uefi'
cpu_name        = sys.argv[2]                                                                                            # Nombre de la CPU
ram_capacity    = sys.argv[3]                                                                                            # Capacidad de la RAM
ram_type        = 1 if sys.argv[4] == 'ddr4' else 2 if sys.argv[4] == 'ddr3' else 3 if sys.argv[4] == 'ddr2' else 4      # Tipo de RAM (ddr2, ddr3, ddr4, ddr5)
disc_type       = 1 if sys.argv[5] == 'hdd' else 2 if sys.argv[5] == 'ssd' else 3                                        # Tipo de disco (HDD, SSD, NVMe)
disc_capacity   = sys.argv[6]                                                                                            # Capacidad del disco
gpu_name        = sys.argv[7] if sys.argv[7]!="Indefinido" else ""                                                       # Nombre de la GPU
gpu_type        = 1 if sys.argv[8]=='integrada' else 2                                                                   # Tipo de GPU (integrada o externa)
wifi            = 1 if sys.argv[9] == 'true' else 2                                                                      # 1 para 'si', 0 para 'no'
bluetooth       = 1 if sys.argv[10] == 'true' else 2                                                                     # 1 para 'si', 0 para 'no'
try:
    observaciones   = sys.argv[11]                                                                                       # Observaciones
except IndexError:
    observaciones = ""                                                                                           # Nombre del ticket
sn_prefix = sys.argv[12]
sn_num = str(sys.argv[13])

sn = f"{sn_prefix}-{padding_num(sn_num)}"
# Crear el diccionario con los valores obtenidos
dic = {
    'sn': sn,
    'board_type': board_type,          # 1: BIOS, 2: UEFI
    'Cuadro de texto 1': cpu_name,              # Nombre de la CPU
    'ram_capacity': ram_capacity,      # Capacidad de la RAM en GB
    'ram_type': ram_type,              # Número asignado al tipo de RAM (3: DDR2, 2: DDR3, 1: DDR4, 4: DDR5)
    'disc_type': disc_type,            # Tipo de disco (1: HDD, 2: SSD, 3: NVMe)
    'disc_capacity': disc_capacity,    # Capacidad del disco en GB
    'gpu_type': gpu_type,              # Tipo de GPU (integrada o externa)
    'gpu_name': gpu_name,              # Nombre de la GPU
    'wifi_bool': wifi,                 # 1 si tiene WiFi, 2 si no
    'bluetooth_bool': bluetooth,       # 1 si tiene Bluetooth, 2 si no
    'obser': observaciones             # Observaciones (texto libre)
}

fillpdfs.write_fillable_pdf("scripts/pdf/plantilla.pdf", f"scripts/pdf/generado.pdf", dic, True)
