#!/bin/bash
# Kullanımı: ./assign-schema.sh Q2 E1
# $1 = Item ID (örnek: Q2)
# $2 = EntitySchema ID (örnek: E1)

ITEM_ID="$1"
SCHEMA_ID="$2"
MW_URL="http://mediawiki.local:90/api.php"

# --- 1️⃣ Login Token Al ---
LOGIN_TOKEN=$(curl -s "$MW_URL?action=query&meta=tokens&type=login&format=json" | \
    jq -r '.query.tokens.logintoken')

echo $LOGIN_TOKEN

# --- 2️⃣ Clientlogin ---
# curl -s -c cookies.txt -b cookies.txt -X POST "$MW_URL?action=clientlogin&format=json" \
#     -d "username=Admin" \
#     -d "password=Mbry8992@" \
#     -d "logintoken=$LOGIN_TOKEN" \
#     -d "loginreturnurl=$MW_URL" \
#     -d "rememberMe=1" > /dev/null

LOGIN_RESULT=$(curl -s -L --compressed -c cookies.txt -b cookies.txt -X POST "$MW_URL?action=clientlogin&format=json" \
    -d "username=Admin" \
    -d "password=Mbry8992@" \
    -d "logintoken=$LOGIN_TOKEN" \
    -d "loginreturnurl=$MW_URL" \
    -d "rememberMe=1")

echo "$LOGIN_RESULT"


# --- 3️⃣ CSRF Token Al ---
CSRF_TOKEN=$(curl -s -b cookies.txt "$MW_URL?action=query&meta=tokens&type=csrf&format=json" | \
    jq -r '.query.tokens.csrftoken')

# --- 4️⃣ EntitySchema Ata ---
#curl -s -b cookies.txt -X POST "$MW_URL?action=wbeditentity&format=json" \
#    -d "id=$ITEM_ID" \
#    -d "data={\"type\":\"item\",\"id\":\"$ITEM_ID\",\"schemas\":[\"$SCHEMA_ID\"]}" \
#    -d "token=$CSRF_TOKEN"

#echo ""
#echo "✅ $ITEM_ID item'ına $SCHEMA_ID şeması atandı!"

