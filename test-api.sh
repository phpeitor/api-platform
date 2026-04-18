#!/bin/bash
# Script de pruebas para API RENIEC

TOKEN="token_qs0CnOvCCPLioThNWDEIdxfYp1nOx9emM9s1NLRU8u0IMvy5jUuLXFg2BTxK"
BASE_URL="http://127.0.0.1:9010"

echo "╔════════════════════════════════════════════════╗"
echo "║       PRUEBAS API RENIEC CON AUTENTICACIÓN    ║"
echo "╚════════════════════════════════════════════════╝"
echo ""

# TEST 1: Sin token (debe fallar)
echo "TEST 1: SIN TOKEN (debe retornar 401)"
echo "════════════════════════════════════════════════════════"
curl -s -w "\nStatus: %{http_code}\n\n" "$BASE_URL/api/reniec/46798772" | head -c 200
echo ""

# TEST 2: Con token inválido
echo "TEST 2: CON TOKEN INVÁLIDO (debe retornar 403)"
echo "════════════════════════════════════════════════════════"
curl -s -H "Authorization: Bearer token_invalido" \
     -w "\nStatus: %{http_code}\n\n" \
     "$BASE_URL/api/reniec/46798772" | head -c 200
echo ""

# TEST 3: Con token válido
echo "TEST 3: CON TOKEN VÁLIDO (debe retornar 200)"
echo "════════════════════════════════════════════════════════"
curl -s -H "Authorization: Bearer $TOKEN" \
     "$BASE_URL/api/reniec/46798772" | python3 -m json.tool
echo ""

# TEST 4: Health check (sin token)
echo "TEST 4: HEALTH CHECK SIN TOKEN"
echo "════════════════════════════════════════════════════════"
curl -s "$BASE_URL/api/health" | python3 -m json.tool
echo ""

# TEST 5: Documentación API
echo "TEST 5: RUTA DE DOCUMENTACIÓN"
echo "════════════════════════════════════════════════════════"
echo "URL: $BASE_URL/api/docs"
echo "Abre esta URL en tu navegador para ver la documentación interactiva"
echo ""

# TEST 6: Con DNI diferente
echo "TEST 6: CONSULTAR OTRO DNI (48118043)"
echo "════════════════════════════════════════════════════════"
curl -s -H "Authorization: Bearer $TOKEN" \
     "$BASE_URL/api/reniec/48118043" | python3 -m json.tool | head -30
echo ""

echo "✅ Pruebas completadas"
