include /opt/fpp/src/makefile.defines

PLUGIN_NAME = fpp-shelly
TARGET = lib$(PLUGIN_NAME).so

CXXFLAGS += -I/opt/fpp/src -std=c++20 -fPIC
LDFLAGS += -shared -lcurl

SRCS = FPPShelly.cpp
OBJS = $(SRCS:.cpp=.o)

all: $(TARGET)

$(TARGET): $(OBJS)
	$(CXX) $(CXXFLAGS) -o $@ $(OBJS) $(LDFLAGS)

%.o: %.cpp
	$(CXX) $(CXXFLAGS) -c $< -o $@

clean:
	rm -f $(OBJS) $(TARGET)
