import sys
target = sys.argv[1]
hex_data = sys.argv[2]
with open(target, 'wb') as f:
    f.write(bytes.fromhex(hex_data))
print('SUCCESS WRITE', target)
